<?php

namespace Hashtopolis\inc\jobs;

use Exception;
use Hashtopolis\dba\Factory;
use Hashtopolis\dba\models\BackgroundJob;
use Hashtopolis\dba\models\CrackerBinary;
use Hashtopolis\dba\models\CrackerBinaryType;
use Hashtopolis\dba\models\HashType;
use Hashtopolis\dba\QueryFilter;
use Hashtopolis\inc\defines\DBackgroundJobStatus;
use Hashtopolis\inc\defines\DBackgroundJobType;
use Hashtopolis\inc\utils\BackgroundJobUtils;
use Hashtopolis\inc\utils\CrackerScanUtils;
use Hashtopolis\inc\utils\CrackerUtils;
use Hashtopolis\TestBase;
use Override;

require_once(dirname(__FILE__) . '/../../TestBase.php');
require_once(dirname(__FILE__) . '/../../../../src/inc/startup/include.php');

/**
 * End-to-end tests of the scan_cracker background job. The cracker binary
 * archives are fakes: a shell script named '<binaryName>.bin' which echoes a
 * canned '--example-hashes --machine-readable' report, packed into a 7z
 * archive. No hashcat binary is needed
 * to run them.
 */
final class ScanCrackerJobTest extends TestBase {
  private const SEVEN_ZIP_MAGIC = "\x37\x7A\xBC\xAF\x27\x1C";

  /** @var int[] ids of the hashtypes the scan of the tests creates */
  private array $scanHashtypeIds = [99901, 99902, 99903];

  /** @var int[] ids of the binaries the tests created, for archive cleanup */
  private array $testBinaryIds = [];

  /** canned '--example-hashes --machine-readable' report of the fake cracker binary */
  private const MODES_OUTPUT_V1 = 'hashcat (v9.9.9) starting in autodetect mode' . "\n" .
    "\n" .
    '{"0": { "name": "MD5", "slow_hash": false, "is_salted": false, "salt_type": null },' .
    ' "99901": { "name": "Fake Mode One", "slow_hash": false, "is_salted": false, "salt_type": null },' .
    ' "99902": { "name": "Fake Salted Mode", "slow_hash": true, "is_salted": true, "salt_type": "generic" }}' . "\n";

  /** second version of the fake binary: one mode dropped, one new added */
  private const MODES_OUTPUT_V2 = 'hashcat (v9.9.9) starting in autodetect mode' . "\n" .
    "\n" .
    '{"0": { "name": "MD5", "slow_hash": false, "is_salted": false, "salt_type": null },' .
    ' "99902": { "name": "Fake Salted Mode", "slow_hash": true, "is_salted": true, "salt_type": "generic" },' .
    ' "99903": { "name": "Fake Mode Three", "slow_hash": false, "is_salted": true, "salt_type": "embedded" }}' . "\n";

  // Returns the hashcat cracker binary type, creating it first when the
  // initial data does not contain it.
  private function hashcatType(): CrackerBinaryType {
    $qF = new QueryFilter(CrackerBinaryType::TYPE_NAME, CrackerUtils::HASHCAT_BINARY_TYPE, "=");
    $type = Factory::getCrackerBinaryTypeFactory()->filter([Factory::FILTER => $qF], true);
    if ($type === null) {
      $type = $this->createDatabaseObject(
        Factory::getCrackerBinaryTypeFactory(),
        new CrackerBinaryType(null, CrackerUtils::HASHCAT_BINARY_TYPE, 1)
      );
    }
    return $type;
  }

  /**
   * Builds a fake cracker binary archive: a shell script '<binaryName>.bin'
   * which echoes the given output when started with
   * '--example-hashes --machine-readable', packed with 7z.
   */
  private function buildFakeArchive(string $binaryName, string $modesOutput): string {
    $dir = sys_get_temp_dir() . '/hashtopolis-fake-cracker-' . uniqid();
    mkdir($dir);
    file_put_contents($dir . '/' . $binaryName . '.bin',
      "#!/bin/sh\n" .
      "if [ \"\$1\" = '--example-hashes' ] && [ \"\$2\" = '--machine-readable' ]; then\n" .
      "  cat <<'HTP_EOF'\n" . $modesOutput . "HTP_EOF\n" .
      "  exit 0\n" .
      "fi\n" .
      "exit 1\n"
    );
    chmod($dir . '/' . $binaryName . '.bin', 0755);
    $archive = $dir . '.7z';
    exec('7z a ' . escapeshellarg($archive) . ' ' . escapeshellarg($dir) . ' > /dev/null', $out, $rc);
    $this->assertSame(0, $rc, 'packing the fake cracker archive with 7z failed');
    exec('rm -rf ' . escapeshellarg($dir));
    return $archive;
  }

  /**
   * @return BackgroundJob[] pending scan jobs of the given binary
   * @throws Exception
   */
  private function pendingScanJobs(int $binaryId): array {
    $jobs = [];
    foreach ($this->scanJobsOfBinary($binaryId) as $job) {
      if ($job->getStatus() == DBackgroundJobStatus::PENDING) {
        $jobs[] = $job;
      }
    }
    return $jobs;
  }

  /**
   * @return BackgroundJob[] all scan jobs of the given binary
   * @throws Exception
   */
  private function scanJobsOfBinary(int $binaryId): array {
    $jobs = [];
    foreach (Factory::getBackgroundJobFactory()->filter([
      Factory::FILTER => new QueryFilter(BackgroundJob::JOB_TYPE, DBackgroundJobType::SCAN_CRACKER, "=")
    ]) as $job) {
      $payload = json_decode($job->getPayload() ?? "{}", true);
      if (($payload[CrackerBinary::CRACKER_BINARY_ID] ?? null) === $binaryId) {
        $jobs[] = $job;
      }
    }
    return $jobs;
  }

  /**
   * Deletes all scan jobs of the given binary, so tests do not leave pending
   * jobs behind which a later runner run would try to execute on the already
   * deleted test binary.
   * @throws Exception
   */
  private function cleanupScanJobs(int $binaryId): void {
    foreach ($this->scanJobsOfBinary($binaryId) as $job) {
      Factory::getBackgroundJobFactory()->delete($job);
    }
  }

  private function associatedHashtypeIds(int $binaryId): array {
    $ids = [];
    foreach (CrackerUtils::getHashtypesOfBinary($binaryId) as $hashtype) {
      $ids[] = $hashtype->getId();
    }
    sort($ids);
    return $ids;
  }

  /**
   * Removes the scan jobs and the hashtypes the test created and the local
   * archive of the test binaries, also when the test failed on the way.
   */
  #[Override]
  protected function tearDown(): void {
    foreach ($this->testBinaryIds as $binaryId) {
      $this->cleanupScanJobs($binaryId);
      // remove leftover local archives of binaries the test did not clean up
      foreach (glob(CrackerUtils::getCrackersPath() . $binaryId . '_*') ?: [] as $path) {
        unlink($path);
      }
    }
    $this->testBinaryIds = [];
    foreach ($this->scanHashtypeIds as $hashtypeId) {
      $hashtype = Factory::getHashTypeFactory()->get($hashtypeId);
      if ($hashtype !== null) {
        Factory::getHashTypeFactory()->delete($hashtype);
      }
    }
    parent::tearDown();
  }

  private function trackBinary(\Hashtopolis\dba\models\CrackerBinary $binary): void {
    $this->testBinaryIds[] = $binary->getId();
  }

  /**
   * Full run: creating a hashcat binary queues a scan, the scan populates
   * the associations from the mode report of the binary and creates
   * hashtypes for modes which do not exist yet.
   *
   * @throws Exception
   */
  public function testScanPopulatesAssociationsAndCreatesHashtypes(): void {
    $archive = $this->buildFakeArchive('fakecracker', self::MODES_OUTPUT_V1);
    $binary = CrackerUtils::createBinaryFromUpload(
      '1.0.0', 'fakecracker', $this->hashcatType()->getId(), 'inline',
      base64_encode(file_get_contents($archive)), 1
    );
    $this->registerDatabaseObject(Factory::getCrackerBinaryFactory(), $binary);
    $this->trackBinary($binary);

    // until the scan ran, the binary has no associations, but the scan is queued
    $this->assertEquals([], $this->associatedHashtypeIds($binary->getId()));
    $jobs = $this->pendingScanJobs($binary->getId());
    $this->assertCount(1, $jobs);

    BackgroundJobRunner::run();

    $job = Factory::getBackgroundJobFactory()->get($jobs[0]->getId());
    $this->assertSame(DBackgroundJobStatus::DONE, $job->getStatus(), $job->getResultMessage());
    $this->assertSame(0, $job->getExitCode());

    // mode 0 exists already, the two fake modes were created
    $this->assertEquals([0, 99901, 99902], $this->associatedHashtypeIds($binary->getId()));
    $created = Factory::getHashTypeFactory()->get(99901);
    $this->assertNotNull($created);
    $this->assertSame('Fake Mode One', $created->getDescription());
    // the flags of the mode report are taken over on creation
    $this->assertSame(0, $created->getIsSalted());
    $this->assertSame(0, $created->getIsSlowHash());
    $salted = Factory::getHashTypeFactory()->get(99902);
    $this->assertNotNull($salted);
    $this->assertSame('Fake Salted Mode', $salted->getDescription());
    // only a generic salt counts as salted, and the slow-hash flag is taken
    // over from the report
    $this->assertSame(1, $salted->getIsSalted());
    $this->assertSame(1, $salted->getIsSlowHash());

    CrackerUtils::deleteBinary($binary->getId());
    $this->cleanupScanJobs($binary->getId());
  }

  /**
   * A re-scan only applies the difference: modes the new binary version
   * dropped are removed from the associations, new ones are added, common
   * ones are kept.
   *
   * @throws Exception
   */
  public function testRescanAppliesOnlyTheDifference(): void {
    $archive = $this->buildFakeArchive('fakecracker', self::MODES_OUTPUT_V1);
    $binary = CrackerUtils::createBinaryFromUpload(
      '1.0.0', 'fakecracker', $this->hashcatType()->getId(), 'inline',
      base64_encode(file_get_contents($archive)), 1
    );
    $this->registerDatabaseObject(Factory::getCrackerBinaryFactory(), $binary);
    $this->trackBinary($binary);
    BackgroundJobRunner::run();
    $this->cleanupScanJobs($binary->getId());
    $this->assertEquals([0, 99901, 99902], $this->associatedHashtypeIds($binary->getId()));

    // replace the local archive with the second version and scan again
    $archiveV2 = $this->buildFakeArchive('fakecracker', self::MODES_OUTPUT_V2);
    rename($archiveV2, CrackerScanUtils::getLocalArchivePath($binary));
    CrackerUtils::checkCrackerBinary($binary->getId());
    BackgroundJobRunner::run();

    // 99901 was dropped, 99903 was added, 0 and 99902 were kept
    $this->assertEquals([0, 99902, 99903], $this->associatedHashtypeIds($binary->getId()));
    // the hashtypes themselves are not deleted by the scan
    $this->assertNotNull(Factory::getHashTypeFactory()->get(99901));

    CrackerUtils::deleteBinary($binary->getId());
    $this->cleanupScanJobs($binary->getId());
  }

  /**
   * Existing hashtypes are never altered by the scan, it only decides
   * whether the binary supports them or not.
   *
   * @throws Exception
   */
  public function testScanDoesNotAlterExistingHashtypes(): void {
    // pre-existing hashtype 99901 with values the scan would not set
    $this->createDatabaseObject(
      Factory::getHashTypeFactory(),
      new HashType(99901, 'Pre-existing Description', 1, 1)
    );

    $archive = $this->buildFakeArchive('fakecracker', self::MODES_OUTPUT_V1);
    $binary = CrackerUtils::createBinaryFromUpload(
      '1.0.0', 'fakecracker', $this->hashcatType()->getId(), 'inline',
      base64_encode(file_get_contents($archive)), 1
    );
    $this->registerDatabaseObject(Factory::getCrackerBinaryFactory(), $binary);
    $this->trackBinary($binary);
    BackgroundJobRunner::run();

    // the pre-existing hashtype got associated, but its fields are untouched
    $this->assertContains(99901, $this->associatedHashtypeIds($binary->getId()));
    $hashtype = Factory::getHashTypeFactory()->get(99901);
    $this->assertSame('Pre-existing Description', $hashtype->getDescription());
    $this->assertSame(1, $hashtype->getIsSalted());
    $this->assertSame(1, $hashtype->getIsSlowHash());

    CrackerUtils::deleteBinary($binary->getId());
    $this->cleanupScanJobs($binary->getId());
  }

  /**
   * Binaries of a non-hashcat type are not scanned at all, their hashtypes
   * are associated manually.
   *
   * @throws Exception
   */
  public function testNonHashcatBinaryIsNotScanned(): void {
    $type = $this->createCrackerBinaryType();
    $binary = CrackerUtils::createBinaryFromUpload(
      '1.0.0', 'fakecracker', $type->getId(), 'inline',
      base64_encode(self::SEVEN_ZIP_MAGIC . 'generic'), 1
    );
    $this->registerDatabaseObject(Factory::getCrackerBinaryFactory(), $binary);
    $this->trackBinary($binary);

    $this->assertEquals([], $this->associatedHashtypeIds($binary->getId()));
    $this->assertCount(0, $this->pendingScanJobs($binary->getId()));

    // deleteBinary also removes the local archive of the binary
    CrackerUtils::deleteBinary($binary->getId());
  }

  /**
   * A scan job of a binary which does not exist anymore when it runs is
   * recorded as failed without crashing the runner.
   *
   * @throws Exception
   */
  public function testScanOfDeletedBinaryFailsGracefully(): void {
    $archive = $this->buildFakeArchive('fakecracker', self::MODES_OUTPUT_V1);
    $binary = CrackerUtils::createBinaryFromUpload(
      '1.0.0', 'fakecracker', $this->hashcatType()->getId(), 'inline',
      base64_encode(file_get_contents($archive)), 1
    );
    $this->assertGreaterThanOrEqual(1, sizeof($this->pendingScanJobs($binary->getId())));

    // delete the binary and its scan jobs before the scan runs
    $binaryId = $binary->getId();
    CrackerUtils::deleteBinary($binaryId);
    $this->cleanupScanJobs($binaryId);

    // enqueue a scan for the now missing binary directly
    BackgroundJobUtils::enqueue(
      DBackgroundJobType::SCAN_CRACKER, [CrackerBinary::CRACKER_BINARY_ID => $binaryId], null
    );
    BackgroundJobRunner::run();

    $this->assertCount(0, $this->pendingScanJobs($binaryId));
    // the failed job is recorded and did not crash the runner
    $found = false;
    foreach ($this->scanJobsOfBinary($binaryId) as $job) {
      $this->assertSame(DBackgroundJobStatus::FAILED, $job->getStatus());
      $this->assertSame(-1, $job->getExitCode());
      $found = true;
    }
    $this->assertTrue($found, 'the failed scan job was not recorded');
    $this->cleanupScanJobs($binaryId);
  }
}
