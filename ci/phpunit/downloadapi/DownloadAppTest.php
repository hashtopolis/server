<?php

namespace Hashtopolis\downloadapi;

use Hashtopolis\dba\Factory;
use Hashtopolis\dba\models\Agent;
use Hashtopolis\dba\models\CrackerBinary;
use Hashtopolis\dba\models\CrackerBinaryType;
use Hashtopolis\inc\defines\DDirectories;
use Hashtopolis\inc\downloadapi\DownloadApp;
use Hashtopolis\inc\utils\CrackerUtils;
use Hashtopolis\TestBase;
use Override;
use Psr\Http\Message\ResponseInterface;
use Slim\Psr7\Factory\ServerRequestFactory;

require_once(dirname(__FILE__) . '/../TestBase.php');
require_once(dirname(__FILE__) . '/../../../src/inc/startup/include.php');

/**
 * Unit tests for the download endpoint. Verifies the dual mode authentication
 * (agent token or apiv2 JWT), the crackerBinary handler and the streaming of
 * the locally stored archives.
 */
final class DownloadAppTest extends TestBase {
  private const SEVEN_ZIP_MAGIC = "\x37\x7A\xBC\xAF\x27\x1C";

  private CrackerBinaryType $type;
  private CrackerBinary $localBinary;
  private CrackerBinary $externalBinary;
  private string $agentToken;
  private string $archiveContent;
  private bool $savedHttpRange = false;
  private string|false $savedHttpRangeValue = false;

  #[Override]
  protected function setUp(): void {
    parent::setUp();

    $this->type = $this->createDatabaseObject(
      Factory::getCrackerBinaryTypeFactory(),
      new CrackerBinaryType(null, 'download-test-type', 1)
    );
    $this->externalBinary = $this->createDatabaseObject(
      Factory::getCrackerBinaryFactory(),
      new CrackerBinary(null, $this->type->getId(), '1.0.0', 'http://example.com/hc.7z', 'testcracker', null, 1)
    );

    // create a locally stored binary through the import source
    $this->agentToken = 'dl-test-' . uniqid();
    $this->createDatabaseObject(
      Factory::getAgentFactory(),
      new Agent(null, 'download-test-agent', '', 0, '', '', 0, 0, 0, $this->agentToken, '', 0, '', null, 0, '')
    );

    $importName = 'download-test-' . uniqid() . '.7z';
    $this->archiveContent = self::SEVEN_ZIP_MAGIC . 'download-test-content';
    file_put_contents(self::getImportPath() . $importName, $this->archiveContent);
    $this->localBinary = CrackerUtils::createBinaryFromUpload('7.2.7', 'testcracker', $this->type->getId(), 'import', $importName, 1);
    $this->registerDatabaseObject(Factory::getCrackerBinaryFactory(), $this->localBinary);

    if (isset($_SERVER['HTTP_RANGE'])) {
      $this->savedHttpRange = true;
      $this->savedHttpRangeValue = $_SERVER['HTTP_RANGE'];
    }
  }

  #[Override]
  protected function tearDown(): void {
    // remove the archive in case a test failed before it could clean up
    $archive = CrackerUtils::getCrackersPath() . $this->localBinary->getId() . '_' . $this->localBinary->getFilename();
    if (file_exists($archive)) {
      unlink($archive);
    }
    if ($this->savedHttpRange) {
      $_SERVER['HTTP_RANGE'] = $this->savedHttpRangeValue;
    }
    else {
      unset($_SERVER['HTTP_RANGE']);
    }
    parent::tearDown();
  }

  private static function getImportPath(): string {
    return Factory::getStoredValueFactory()->get(DDirectories::IMPORT)->getVal() . '/';
  }

  private function runDownloadRequest(string $uriWithQuery, array $headers = []): ResponseInterface {
    $request = (new ServerRequestFactory())->createServerRequest('GET', $uriWithQuery);
    foreach ($headers as $name => $value) {
      $request = $request->withHeader($name, $value);
    }
    return DownloadApp::create()->handle($request);
  }

  private function localBinaryUri(): string {
    return '/api/download.php/crackerBinary/' . $this->localBinary->getId();
  }

  // A request without any authentication is rejected with 401.
  public function testNoAuthenticationIsRejected(): void {
    $response = $this->runDownloadRequest($this->localBinaryUri());
    $this->assertEquals(401, $response->getStatusCode());
    $this->assertEquals('No access!', (string)$response->getBody());
  }

  // A request with an invalid agent token is rejected with 401.
  public function testInvalidAgentTokenIsRejected(): void {
    $response = $this->runDownloadRequest($this->localBinaryUri() . '?token=invalid-token');
    $this->assertEquals(401, $response->getStatusCode());
    $this->assertEquals('No access!', (string)$response->getBody());
  }

  // A request with an invalid JWT is rejected with 401.
  public function testInvalidBearerTokenIsRejected(): void {
    $response = $this->runDownloadRequest($this->localBinaryUri(), ['Authorization' => 'Bearer invalid.jwt.value']);
    $this->assertEquals(401, $response->getStatusCode());
  }

  // A valid agent token allows to download the archive of a locally stored
  // binary, including the download headers.
  public function testAgentTokenCanDownloadArchive(): void {
    $response = $this->runDownloadRequest($this->localBinaryUri() . '?token=' . $this->agentToken);
    $this->assertEquals(200, $response->getStatusCode());
    $this->assertEquals($this->archiveContent, (string)$response->getBody());
    $this->assertEquals('application/x-7z-compressed', $response->getHeaderLine('Content-Type'));
    $this->assertEquals(
      'attachment; filename="' . $this->localBinary->getFilename() . '"',
      $response->getHeaderLine('Content-Disposition')
    );
    $this->assertEquals(strlen($this->archiveContent), (int)$response->getHeaderLine('Content-Length'));
  }

  // An unknown download kind is rejected with 404.
  public function testUnknownKindIsRejected(): void {
    $response = $this->runDownloadRequest('/api/download.php/unknown/' . $this->localBinary->getId() . '?token=' . $this->agentToken);
    $this->assertEquals(404, $response->getStatusCode());
    $this->assertEquals('Unknown download kind!', (string)$response->getBody());
  }

  // A non existing binary id is rejected with 404.
  public function testUnknownBinaryIdIsRejected(): void {
    $response = $this->runDownloadRequest('/api/download.php/crackerBinary/99999999?token=' . $this->agentToken);
    $this->assertEquals(404, $response->getStatusCode());
  }

  // Binaries which are not locally stored have no archive to download.
  public function testExternalBinaryHasNoArchive(): void {
    $response = $this->runDownloadRequest('/api/download.php/crackerBinary/' . $this->externalBinary->getId() . '?token=' . $this->agentToken);
    $this->assertEquals(404, $response->getStatusCode());
    $this->assertEquals('No such cracker binary archive!', (string)$response->getBody());
  }

  // When the archive is not present on the server anymore, the download
  // results in 404.
  public function testMissingArchiveFileIsRejected(): void {
    $archive = CrackerUtils::getCrackersPath() . $this->localBinary->getId() . '_' . $this->localBinary->getFilename();
    unlink($archive);
    $response = $this->runDownloadRequest($this->localBinaryUri() . '?token=' . $this->agentToken);
    $this->assertEquals(404, $response->getStatusCode());
    $this->assertEquals('The archive of this cracker binary is not present on the server!', (string)$response->getBody());
  }

  // A range request is answered with partial content.
  public function testRangeRequestReturnsPartialContent(): void {
    $_SERVER['HTTP_RANGE'] = 'bytes=0-5';
    $response = $this->runDownloadRequest($this->localBinaryUri() . '?token=' . $this->agentToken);
    $this->assertEquals(206, $response->getStatusCode());
    $this->assertEquals(substr($this->archiveContent, 0, 6), (string)$response->getBody());
    $this->assertEquals('bytes 0-5/' . strlen($this->archiveContent), $response->getHeaderLine('Content-Range'));
  }

  // A request with a matching ETag is answered with not modified.
  public function testMatchingEtagReturnsNotModified(): void {
    $archive = CrackerUtils::getCrackersPath() . $this->localBinary->getId() . '_' . $this->localBinary->getFilename();
    $etag = md5(filemtime($archive) . strlen($this->archiveContent));
    $response = $this->runDownloadRequest($this->localBinaryUri() . '?token=' . $this->agentToken, ['If-None-Match' => $etag]);
    $this->assertEquals(304, $response->getStatusCode());
  }
}
