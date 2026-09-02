<?php

namespace Hashtopolis\dba;

use Exception;
use Hashtopolis\dba\models\AccessGroup;
use Hashtopolis\dba\models\Hash;
use Hashtopolis\dba\models\HashBinary;
use Hashtopolis\dba\models\HashType;
use Hashtopolis\dba\models\Hashlist;
use Hashtopolis\inc\defines\DHashlistFormat;
use Hashtopolis\TestBase;
use RuntimeException;

require_once(dirname(__FILE__) . '/../TestBase.php');

/**
 * Tests for AbstractModelFactory::columnTimeseriesFilter().
 *
 * The scenarios below mirror the patterns used in GetCracksPerDayHelperAPI:
 *   - Hash table joined with Hashlist for ACL-filtering via access group.
 *   - HashBinary table joined with Hashlist for the same ACL pattern.
 *
 * Unix timestamps are chosen at UTC noon to avoid date-boundary issues when
 * the database server converts them to local-time dates.
 */
final class ColumnTimeseriesFilterTest extends TestBase {

  // -----------------------------------------------------------------------
  // Helpers
  // -----------------------------------------------------------------------

  /**
   * Unix timestamp for "noon UTC" on the supplied ISO date (YYYY-MM-DD).
   */
  private function noonOn(string $date): int {
    return strtotime($date . ' 12:00:00 UTC');
  }

  /**
   * Create a Hash row.
   *
   * @throws Exception
   */
  private function createHash(Hashlist $hashlist, int $isCracked, int $timeCracked): Hash {
    $hash = $this->createDatabaseObject(
      Factory::getHashFactory(),
      new Hash(null, $hashlist->getId(), 'hash_' . uniqid(), '', '', $timeCracked, null, $isCracked, 0)
    );
    $this->assertInstanceOf(Hash::class, $hash);
    return $hash;
  }

  /**
   * Create a HashBinary row.
   *
   * @throws Exception
   */
  private function createHashBinary(Hashlist $hashlist, int $isCracked, int $timeCracked): HashBinary {
    $hb = $this->createDatabaseObject(
      Factory::getHashBinaryFactory(),
      new HashBinary(null, $hashlist->getId(), 'essid_' . uniqid(), 'hash_' . uniqid(), '', $timeCracked, null, $isCracked, 0)
    );
    $this->assertInstanceOf(HashBinary::class, $hb);
    return $hb;
  }

  /**
   * Create a Hashlist using an existing hash type from the fixture database.
   *
   * Some environments have a HashType schema without auto-generated IDs, so we
   * avoid creating new HashType rows in these DBA-focused tests.
   *
   * @throws Exception
   */
  private function createHashlistForGroup(AccessGroup $group): Hashlist {
    $hashTypeIds = Factory::getHashTypeFactory()->columnFilter(
      [Factory::LIMIT => new LimitFilter(1)],
      HashType::HASH_TYPE_ID
    );
    if (empty($hashTypeIds)) {
      throw new RuntimeException('No HashType rows available in test database.');
    }

    $hashlist = $this->createDatabaseObject(
      Factory::getHashlistFactory(),
      new Hashlist(
        null,
        'hashlist_' . uniqid(),
        DHashlistFormat::PLAIN,
        (int)$hashTypeIds[0],
        1,
        ':',
        0,
        0,
        0,
        0,
        $group->getId(),
        '',
        0,
        0,
        0
      )
    );
    $this->assertInstanceOf(Hashlist::class, $hashlist);
    return $hashlist;
  }

  // -----------------------------------------------------------------------
  // columnTimeseriesFilter tests
  // -----------------------------------------------------------------------

  /**
   * Hashes cracked on two different days must produce two distinct date
   * buckets with correct counts.
   *
   * @throws Exception
   */
  public function testBasicDailyGrouping(): void {
    $ag = $this->createAccessGroup('ts_basic');
    $hl = $this->createHashlistForGroup($ag);

    $day1 = $this->noonOn('2023-06-01');
    $day2 = $this->noonOn('2023-06-02');

    $this->createHash($hl, 1, $day1);
    $this->createHash($hl, 1, $day1);
    $this->createHash($hl, 1, $day2);

    $qF1 = new QueryFilter(Hash::IS_CRACKED, 1, "=");
    $qF2 = new ContainFilter(Hash::HASHLIST_ID, [$hl->getId()]);

    $counts = Factory::getHashFactory()->columnTimeseriesFilter(
      [Factory::FILTER => [$qF1, $qF2]],
      Hash::TIME_CRACKED
    );

    $this->assertArrayHasKey('2023-06-01', $counts);
    $this->assertArrayHasKey('2023-06-02', $counts);
    $this->assertEquals(2, $counts['2023-06-01']);
    $this->assertEquals(1, $counts['2023-06-02']);
  }

  /**
   * Uncracked hashes must not appear in the timeseries when filtered by
   * isCracked = 1.
   *
   * @throws Exception
   */
  public function testUncrackedHashesAreExcluded(): void {
    $ag = $this->createAccessGroup('ts_uncracked');
    $hl = $this->createHashlistForGroup($ag);

    $day1 = $this->noonOn('2023-07-10');

    $this->createHash($hl, 1, $day1); // cracked
    $this->createHash($hl, 1, $day1); // cracked
    $this->createHash($hl, 0, $day1); // NOT cracked — must not be counted

    $qF1 = new QueryFilter(Hash::IS_CRACKED, 1, "=");
    $qF2 = new ContainFilter(Hash::HASHLIST_ID, [$hl->getId()]);

    $counts = Factory::getHashFactory()->columnTimeseriesFilter(
      [Factory::FILTER => [$qF1, $qF2]],
      Hash::TIME_CRACKED
    );

    $this->assertArrayHasKey('2023-07-10', $counts);
    $this->assertEquals(2, $counts['2023-07-10']);
  }

  /**
   * Mimics the exact join pattern of GetCracksPerDayHelperAPI:
   *   Hash factory with a JoinFilter on Hashlist (for ACL) and a
   *   ContainFilter on Hashlist.accessGroupId.
   *
   * Hashes belonging to a different access group must be excluded.
   *
   * @throws Exception
   */
  public function testJoinFilterRestrictsToAccessGroup(): void {
    // Access group 1 — the "allowed" group
    $ag1 = $this->createAccessGroup('ts_ag1');
    $hl1 = $this->createHashlistForGroup($ag1);

    // Access group 2 — must not appear in results
    $ag2 = $this->createAccessGroup('ts_ag2');
    $hl2 = $this->createHashlistForGroup($ag2);

    $day = $this->noonOn('2023-08-15');

    $this->createHash($hl1, 1, $day); // allowed
    $this->createHash($hl1, 1, $day); // allowed
    $this->createHash($hl2, 1, $day); // should be excluded by ACL

    // Same query structure as GetCracksPerDayHelperAPI
    $qF1 = new QueryFilter(Hash::IS_CRACKED, 1, "=");
    $qF3 = new ContainFilter(Hashlist::ACCESS_GROUP_ID, [$ag1->getId()], Factory::getHashlistFactory());

    $hashJF = new JoinFilter(Factory::getHashlistFactory(), Hash::HASHLIST_ID, Hashlist::HASHLIST_ID);

    $counts = Factory::getHashFactory()->columnTimeseriesFilter(
      [Factory::FILTER => [$qF1, $qF3], Factory::JOIN => [$hashJF]],
      Hash::TIME_CRACKED
    );

    $this->assertArrayHasKey('2023-08-15', $counts);
    // Only the two hashes from ag1 should be counted
    $this->assertEquals(2, $counts['2023-08-15']);
  }

  /**
   * Multiple cracked hashes on the same day must be aggregated into one
   * bucket with the correct total count.
   *
   * @throws Exception
   */
  public function testMultipleHashesSameDayAreAggregated(): void {
    $ag = $this->createAccessGroup('ts_sameday');
    $hl = $this->createHashlistForGroup($ag);

    $day = $this->noonOn('2023-09-01');

    for ($i = 0; $i < 5; $i++) {
      $this->createHash($hl, 1, $day);
    }

    $qF1 = new QueryFilter(Hash::IS_CRACKED, 1, "=");
    $qF2 = new ContainFilter(Hash::HASHLIST_ID, [$hl->getId()]);

    $counts = Factory::getHashFactory()->columnTimeseriesFilter(
      [Factory::FILTER => [$qF1, $qF2]],
      Hash::TIME_CRACKED
    );

    $this->assertArrayHasKey('2023-09-01', $counts);
    $this->assertEquals(5, $counts['2023-09-01']);
    // Only one key should be present for this hashlist
    $this->assertCount(1, $counts);
  }

  /**
   * An empty result set (no cracked hashes at all) must return an empty array.
   *
   * @throws Exception
   */
  public function testEmptyResultReturnsEmptyArray(): void {
    $ag = $this->createAccessGroup('ts_empty');
    $hl = $this->createHashlistForGroup($ag);

    // Only uncracked hashes — nothing should match isCracked = 1
    $this->createHash($hl, 0, $this->noonOn('2023-10-01'));

    $qF1 = new QueryFilter(Hash::IS_CRACKED, 1, "=");
    $qF2 = new ContainFilter(Hash::HASHLIST_ID, [$hl->getId()]);

    $counts = Factory::getHashFactory()->columnTimeseriesFilter(
      [Factory::FILTER => [$qF1, $qF2]],
      Hash::TIME_CRACKED
    );

    $this->assertEmpty($counts);
  }

  /**
   * Same ACL-join pattern applied to the HashBinary factory — mirrors the
   * second columnTimeseriesFilter call in GetCracksPerDayHelperAPI.
   *
   * @throws Exception
   */
  public function testHashBinaryTimeseriesWithJoinFilter(): void {
    $ag = $this->createAccessGroup('ts_hb_ag1');
    $hl = $this->createHashlistForGroup($ag);

    $agExcluded = $this->createAccessGroup('ts_hb_ag2');
    $hlExcluded = $this->createHashlistForGroup($agExcluded);

    $day1 = $this->noonOn('2023-11-05');
    $day2 = $this->noonOn('2023-11-06');

    $this->createHashBinary($hl, 1, $day1);
    $this->createHashBinary($hl, 1, $day1);
    $this->createHashBinary($hl, 1, $day2);
    $this->createHashBinary($hlExcluded, 1, $day1); // excluded by ACL
    $this->createHashBinary($hl, 0, $day1);          // excluded by isCracked filter

    // Use Hash::IS_CRACKED and Hash::TIME_CRACKED — same string constants as
    // HashBinary ("isCracked" / "timeCracked"), matching GetCracksPerDayHelperAPI.
    $qF1 = new QueryFilter(Hash::IS_CRACKED, 1, "=");
    $qF3 = new ContainFilter(Hashlist::ACCESS_GROUP_ID, [$ag->getId()], Factory::getHashlistFactory());

    $binaryJF = new JoinFilter(Factory::getHashlistFactory(), HashBinary::HASHLIST_ID, Hashlist::HASHLIST_ID);

    $counts = Factory::getHashBinaryFactory()->columnTimeseriesFilter(
      [Factory::FILTER => [$qF1, $qF3], Factory::JOIN => [$binaryJF]],
      Hash::TIME_CRACKED
    );

    $this->assertArrayHasKey('2023-11-05', $counts);
    $this->assertArrayHasKey('2023-11-06', $counts);
    $this->assertEquals(2, $counts['2023-11-05']); // 2 from ag, not the excluded one
    $this->assertEquals(1, $counts['2023-11-06']);
  }

  /**
   * Combined result mimicking the full GetCracksPerDayHelperAPI merge of
   * Hash + HashBinary counts for the same access group and day.
   *
   * Both tables are queried separately and their per-day counts are merged.
   *
   * @throws Exception
   */
  public function testCombinedHashAndHashBinaryCountsMerge(): void {
    $ag = $this->createAccessGroup('ts_combined');
    $hl = $this->createHashlistForGroup($ag);

    $day = $this->noonOn('2023-12-24');

    // 3 cracked Hash rows
    $this->createHash($hl, 1, $day);
    $this->createHash($hl, 1, $day);
    $this->createHash($hl, 1, $day);

    // 2 cracked HashBinary rows
    $this->createHashBinary($hl, 1, $day);
    $this->createHashBinary($hl, 1, $day);

    $qF1 = new QueryFilter(Hash::IS_CRACKED, 1, "=");
    $qF3 = new ContainFilter(Hashlist::ACCESS_GROUP_ID, [$ag->getId()], Factory::getHashlistFactory());

    $hashJF   = new JoinFilter(Factory::getHashlistFactory(), Hash::HASHLIST_ID,       Hashlist::HASHLIST_ID);
    $binaryJF = new JoinFilter(Factory::getHashlistFactory(), HashBinary::HASHLIST_ID, Hashlist::HASHLIST_ID);

    $counts1 = Factory::getHashFactory()->columnTimeseriesFilter(
      [Factory::FILTER => [$qF1, $qF3], Factory::JOIN => [$hashJF]],
      Hash::TIME_CRACKED
    );
    $counts2 = Factory::getHashBinaryFactory()->columnTimeseriesFilter(
      [Factory::FILTER => [$qF1, $qF3], Factory::JOIN => [$binaryJF]],
      Hash::TIME_CRACKED
    );

    // Merge exactly as GetCracksPerDayHelperAPI does
    foreach ($counts2 as $key => $value) {
      $counts1[$key] = ($counts1[$key] ?? 0) + $value;
    }

    $this->assertArrayHasKey('2023-12-24', $counts1);
    $this->assertEquals(5, $counts1['2023-12-24']); // 3 Hash + 2 HashBinary
  }

  /**
   * A time-range filter (timeCracked > $start) must exclude hashes that were
   * cracked before the cutoff — as done in GetCracksPerDayHelperAPI with
   * "$start = time() - 3600 * 24 * 365".
   *
   * @throws Exception
   */
  public function testTimeRangeFilterExcludesOldHashes(): void {
    $ag = $this->createAccessGroup('ts_timerange');
    $hl = $this->createHashlistForGroup($ag);

    $recent = $this->noonOn('2024-01-15');
    $old    = $this->noonOn('2020-01-01'); // well before any sane cutoff

    $this->createHash($hl, 1, $recent);
    $this->createHash($hl, 1, $old);

    $cutoff = strtotime('2023-01-01 00:00:00 UTC');

    $qF1 = new QueryFilter(Hash::IS_CRACKED,   1,       "=");
    $qF2 = new QueryFilter(Hash::TIME_CRACKED, $cutoff, ">");
    $qF3 = new ContainFilter(Hash::HASHLIST_ID, [$hl->getId()]);

    $counts = Factory::getHashFactory()->columnTimeseriesFilter(
      [Factory::FILTER => [$qF1, $qF2, $qF3]],
      Hash::TIME_CRACKED
    );

    $this->assertArrayHasKey('2024-01-15', $counts);
    $this->assertArrayNotHasKey('2020-01-01', $counts);
    $this->assertEquals(1, $counts['2024-01-15']);
  }
}
