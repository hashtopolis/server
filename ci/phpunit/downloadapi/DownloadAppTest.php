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
  private string|false $savedBackendUrl = false;
  private string|false $savedFrontendPort = false;
  private bool $savedHttpRange = false;
  private string|false $savedHttpRangeValue = false;

  #[Override]
  protected function setUp(): void {
    parent::setUp();

    $this->savedBackendUrl = getenv('HASHTOPOLIS_BACKEND_URL');
    $this->savedFrontendPort = getenv('HASHTOPOLIS_FRONTEND_PORT');
    putenv('HASHTOPOLIS_BACKEND_URL=http://localhost/api/v2');

    $suffix = uniqid();

    $this->type = $this->createDatabaseObject(
      Factory::getCrackerBinaryTypeFactory(),
      new CrackerBinaryType(null, 'dl-' . $suffix, 1)
    );
    $this->externalBinary = $this->createDatabaseObject(
      Factory::getCrackerBinaryFactory(),
      new CrackerBinary(null, $this->type->getId(), '1.0.0', 'http://example.com/hc.7z', 'testcracker', null)
    );

    // create a locally stored binary through the import source
    $this->agentToken = 'dl-test-' . uniqid();
    $this->createDatabaseObject(
      Factory::getAgentFactory(),
      new Agent(null, 'download-test-agent-' . $suffix, '', 0, '', '', 0, 0, 0, $this->agentToken, '', 0, '', null, 0, '')
    );

    $importName = 'download-test-' . uniqid() . '.7z';
    $this->archiveContent = self::SEVEN_ZIP_MAGIC . 'download-test-content';
    file_put_contents(self::getImportPath() . $importName, $this->archiveContent);
    $this->localBinary = CrackerUtils::createBinaryFromUpload('7.2.7', 'testcracker', $this->type->getId(), 'import', $importName);
    $this->registerDatabaseObject(Factory::getCrackerBinaryFactory(), $this->localBinary);

    if (isset($_SERVER['HTTP_RANGE'])) {
      $this->savedHttpRange = true;
      $this->savedHttpRangeValue = $_SERVER['HTTP_RANGE'];
    }
  }

  #[Override]
  protected function tearDown(): void {
    try {
      // remove the archive in case a test failed before it could clean up
      $archive = CrackerUtils::getCrackersPath() . $this->localBinary->getId() . '_' . $this->localBinary->getFilename();
      if (file_exists($archive)) {
        unlink($archive);
      }
      parent::tearDown();
    }
    finally {
      if ($this->savedBackendUrl === false) {
        putenv('HASHTOPOLIS_BACKEND_URL');
      }
      else {
        putenv('HASHTOPOLIS_BACKEND_URL=' . $this->savedBackendUrl);
      }
      if ($this->savedFrontendPort === false) {
        putenv('HASHTOPOLIS_FRONTEND_PORT');
      }
      else {
        putenv('HASHTOPOLIS_FRONTEND_PORT=' . $this->savedFrontendPort);
      }
      if ($this->savedHttpRange) {
        $_SERVER['HTTP_RANGE'] = $this->savedHttpRangeValue;
      }
      else {
        unset($_SERVER['HTTP_RANGE']);
      }
    }
  }

  private static function getImportPath(): string {
    return Factory::getStoredValueFactory()->get(DDirectories::IMPORT)->getVal() . '/';
  }

  private function runDownloadRequest(string $uriWithQuery, array $headers = [], string $method = 'GET'): ResponseInterface {
    $request = (new ServerRequestFactory())->createServerRequest($method, $uriWithQuery);
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

  // A backend url with a deployment subpath serves the download routes under
  // that subpath, so the download urls built from it point to a matching route.
  public function testSubpathBackendUrlIsServed(): void {
    putenv('HASHTOPOLIS_BACKEND_URL=https://localhost:8443/hashtopolis/api/v2');
    $response = $this->runDownloadRequest('/hashtopolis' . $this->localBinaryUri() . '?token=' . $this->agentToken);
    $this->assertEquals(200, $response->getStatusCode());
    $this->assertEquals($this->archiveContent, (string)$response->getBody());
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

  // A suffix range returns the requested number of bytes from the end.
  public function testSuffixRangeRequestReturnsFinalBytes(): void {
    $_SERVER['HTTP_RANGE'] = 'bytes=-6';
    $response = $this->runDownloadRequest($this->localBinaryUri() . '?token=' . $this->agentToken);
    $size = strlen($this->archiveContent);
    $this->assertEquals(206, $response->getStatusCode());
    $this->assertEquals(substr($this->archiveContent, -6), (string)$response->getBody());
    $this->assertEquals('bytes ' . ($size - 6) . '-' . ($size - 1) . '/' . $size, $response->getHeaderLine('Content-Range'));
  }

  // An open-ended range returns all bytes through the end of the file.
  public function testOpenEndedRangeRequestReturnsRemainingBytes(): void {
    $_SERVER['HTTP_RANGE'] = 'bytes=6-';
    $response = $this->runDownloadRequest($this->localBinaryUri() . '?token=' . $this->agentToken);
    $size = strlen($this->archiveContent);
    $this->assertEquals(206, $response->getStatusCode());
    $this->assertEquals(substr($this->archiveContent, 6), (string)$response->getBody());
    $this->assertEquals('bytes 6-' . ($size - 1) . '/' . $size, $response->getHeaderLine('Content-Range'));
  }

  // A request with a matching ETag is answered with not modified.
  public function testMatchingEtagReturnsNotModified(): void {
    $archive = CrackerUtils::getCrackersPath() . $this->localBinary->getId() . '_' . $this->localBinary->getFilename();
    $etag = md5(filemtime($archive) . strlen($this->archiveContent));
    $response = $this->runDownloadRequest($this->localBinaryUri() . '?token=' . $this->agentToken, ['If-None-Match' => $etag]);
    $this->assertEquals(304, $response->getStatusCode());
  }

  /**
   * Configure the backend url and frontend port the CORS check of the apiv2 uses,
   * like the docker setup where the web-ui is served from another port.
   */
  private function useCrossOriginSetup(): void {
    putenv('HASHTOPOLIS_BACKEND_URL=http://localhost:8080/api/v2');
    putenv('HASHTOPOLIS_FRONTEND_PORT=4200');
  }

  // The browser preflight of the web-ui (cross origin, Authorization header) is
  // answered without authentication, otherwise the JWT download cannot start.
  public function testPreflightIsAnsweredWithoutAuthentication(): void {
    $this->useCrossOriginSetup();
    $response = $this->runDownloadRequest($this->localBinaryUri(), [
      'Origin' => 'http://localhost:4200',
      'Access-Control-Request-Method' => 'GET',
      'Access-Control-Request-Headers' => 'authorization,x-skip-error-dialog'
    ], 'OPTIONS');
    $this->assertEquals(204, $response->getStatusCode());
    $this->assertEquals('http://localhost:4200', $response->getHeaderLine('Access-Control-Allow-Origin'));
    $this->assertStringContainsString('GET', $response->getHeaderLine('Access-Control-Allow-Methods'));
    $this->assertEquals('authorization,x-skip-error-dialog', $response->getHeaderLine('Access-Control-Allow-Headers'));
  }

  // Error responses carry the CORS headers too, so the web-ui can read the status.
  public function testErrorResponsesCarryCorsHeaders(): void {
    $this->useCrossOriginSetup();
    $response = $this->runDownloadRequest($this->localBinaryUri(), ['Origin' => 'http://localhost:4200']);
    $this->assertEquals(401, $response->getStatusCode());
    $this->assertEquals('http://localhost:4200', $response->getHeaderLine('Access-Control-Allow-Origin'));
  }

  // A successful cross origin download exposes the filename header to the web-ui.
  public function testDownloadCarriesCorsHeaders(): void {
    $this->useCrossOriginSetup();
    $response = $this->runDownloadRequest(
      $this->localBinaryUri() . '?token=' . $this->agentToken,
      ['Origin' => 'http://localhost:4200']
    );
    $this->assertEquals(200, $response->getStatusCode());
    $this->assertEquals('http://localhost:4200', $response->getHeaderLine('Access-Control-Allow-Origin'));
    $this->assertStringContainsString('Content-Disposition', $response->getHeaderLine('Access-Control-Expose-Headers'));
  }

  // An origin which does not match the configured backend is rejected like in the apiv2.
  public function testForeignOriginIsRejected(): void {
    $this->useCrossOriginSetup();
    $response = $this->runDownloadRequest($this->localBinaryUri(), [
      'Origin' => 'http://evil.example:4200',
      'Access-Control-Request-Method' => 'GET'
    ], 'OPTIONS');
    $this->assertEquals(403, $response->getStatusCode());
    $this->assertEquals('', $response->getHeaderLine('Access-Control-Allow-Origin'));
  }

  // Agents send no Origin header, their downloads are unaffected by the CORS check.
  public function testAgentDownloadWithoutOriginIsUnaffected(): void {
    $this->useCrossOriginSetup();
    $response = $this->runDownloadRequest($this->localBinaryUri() . '?token=' . $this->agentToken);
    $this->assertEquals(200, $response->getStatusCode());
    $this->assertEquals($this->archiveContent, (string)$response->getBody());
  }
}
