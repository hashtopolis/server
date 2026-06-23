<?php

namespace Hashtopolis\inc;

use Exception;
use Hashtopolis\dba\Factory;
use Hashtopolis\dba\models\StoredValue;
use Hashtopolis\TestBase;

require_once(dirname(__FILE__) . '/../TestBase.php');

final class UtilTest extends TestBase {
  /**
   * extractFileExtension returns empty string when no dot is present.
   */
  public function testExtractFileExtensionNoExtension(): void {
    $this->assertEquals("", Util::extractFileExtension("filename"));
  }
  
  /**
   * extractFileExtension returns the substring after the last dot.
   */
  public function testExtractFileExtensionSimple(): void {
    $this->assertEquals("txt", Util::extractFileExtension("file.txt"));
  }
  
  /**
   * extractFileExtension handles multiple dots (returns last segment).
   */
  public function testExtractFileExtensionMultipleDots(): void {
    $this->assertEquals("gz", Util::extractFileExtension("archive.tar.gz"));
  }
  
  /**
   * extractFileExtension returns the full name after the dot for hidden files.
   */
  public function testExtractFileExtensionHiddenFile(): void {
    $this->assertEquals("htaccess", Util::extractFileExtension(".htaccess"));
  }
  
  /**
   * extractFileExtension returns empty string for empty input.
   */
  public function testExtractFileExtensionEmpty(): void {
    $this->assertEquals("", Util::extractFileExtension(""));
  }
  
  /**
   * texEscape leaves normal text unchanged.
   */
  public function testTexEscapeNormalText(): void {
    $this->assertEquals("hello world", Util::texEscape("hello world"));
  }
  
  /**
   * texEscape escapes hash characters.
   */
  public function testTexEscapeHash(): void {
    $this->assertEquals("\\#", Util::texEscape("#"));
  }
  
  /**
   * texEscape escapes backslashes.
   */
  public function testTexEscapeBackslash(): void {
    $this->assertEquals("\\textbackslash", Util::texEscape("\\"));
  }
  
  /**
   * texEscape escapes underscores.
   */
  public function testTexEscapeUnderscore(): void {
    $this->assertEquals("\\_", Util::texEscape("_"));
  }
  
  /**
   * texEscape handles mixed special characters.
   */
  public function testTexEscapeMixed(): void {
    $this->assertEquals("\\_\\#\\textbackslash", Util::texEscape("_#\\"));
  }
  
  /**
   * texEscape returns empty string for empty input.
   */
  public function testTexEscapeEmpty(): void {
    $this->assertEquals("", Util::texEscape(""));
  }
  
  /**
   * bintohex converts binary string to hex pairs.
   */
  public function testBintohex(): void {
    $this->assertEquals("48656c6c6f", Util::bintohex("Hello"));
  }
  
  /**
   * bintohex returns empty string for empty input.
   */
  public function testBintohexEmpty(): void {
    $this->assertEquals("", Util::bintohex(""));
  }
  
  /**
   * bintohex zero-pads single-digit hex values.
   */
  public function testBintohexZeroPad(): void {
    $this->assertEquals("00ff", Util::bintohex("\x00\xff"));
  }
  
  /**
   * tickdone returns empty string when progress is less than total.
   */
  public function testTickdoneIncomplete(): void {
    $this->assertEquals("", Util::tickdone(5, 10));
  }
  
  /**
   * tickdone returns check span when progress equals total.
   */
  public function testTickdoneComplete(): void {
    $this->assertEquals(' <span class="fas fa-check" aria-hidden="true"></span>', Util::tickdone(10, 10));
  }
  
  /**
   * tickdone returns check span when progress exceeds total.
   */
  public function testTickdoneOverflow(): void {
    $this->assertEquals(' <span class="fas fa-check" aria-hidden="true"></span>', Util::tickdone(15, 10));
  }
  
  /**
   * tickdone returns empty string when total is zero (avoid division by zero).
   */
  public function testTickdoneZeroTotal(): void {
    $this->assertEquals("", Util::tickdone(0, 0));
  }
  
  /**
   * sectotime formats zero seconds.
   */
  public function testSectotimeZero(): void {
    $this->assertEquals("00:00:00", Util::sectotime(0));
  }
  
  /**
   * sectotime formats less than one day.
   */
  public function testSectotimeLessThanDay(): void {
    $this->assertEquals("12:34:56", Util::sectotime(12 * 3600 + 34 * 60 + 56));
  }
  
  /**
   * sectotime formats exactly one day (condition uses > 86400, so 86401 triggers it).
   */
  public function testSectotimeJustOverOneDay(): void {
    $this->assertEquals("1d 00:00:01", Util::sectotime(86401));
  }
  
  /**
   * sectotime formats more than one day.
   */
  public function testSectotimeMultipleDays(): void {
    $this->assertEquals("3d 05:30:00", Util::sectotime(3 * 86400 + 5 * 3600 + 1800));
  }
  
  /**
   * escapeSpecial escapes double quotes to HTML entity (htmlentities converts " to &quot; first).
   */
  public function testEscapeSpecialDoubleQuote(): void {
    $this->assertStringContainsString("&quot;", Util::escapeSpecial('"'));
  }
  
  /**
   * escapeSpecial escapes single quotes to HTML entity (htmlentities converts ' to &#039; first).
   */
  public function testEscapeSpecialSingleQuote(): void {
    $this->assertStringContainsString("&#039;", Util::escapeSpecial("'"));
  }
  
  /**
   * escapeSpecial escapes backticks to HTML entity.
   */
  public function testEscapeSpecialBacktick(): void {
    $this->assertStringContainsString("&#96;", Util::escapeSpecial("`"));
  }
  
  /**
   * escapeSpecial returns htmlentities for normal text.
   */
  public function testEscapeSpecialNormal(): void {
    $this->assertEquals("a &amp; b", Util::escapeSpecial("a & b"));
  }
  
  /**
   * nicenum returns 0.00 with default scale (always rounded to 2 decimals).
   */
  public function testNicenumZero(): void {
    $this->assertEquals("0.00 ", Util::nicenum(0));
  }
  
  /**
   * nicenum stays in base unit below threshold (condition uses >, not >=).
   */
  public function testNicenumBelowThreshold(): void {
    $this->assertEquals("1023.00 ", Util::nicenum(1023));
  }
  
  /**
   * nicenum stays in base unit at exact threshold because condition is > not >=.
   */
  public function testNicenumAtThreshold(): void {
    $this->assertEquals("1024.00 ", Util::nicenum(1024));
  }
  
  /**
   * nicenum switches to k above threshold.
   */
  public function testNicenumK(): void {
    $this->assertEquals("1.00 k", Util::nicenum(1025));
  }
  
  /**
   * nicenum switches to M.
   */
  public function testNicenumM(): void {
    $this->assertEquals("1.00 M", Util::nicenum(1048576 + 1));
  }
  
  /**
   * nicenum switches to G.
   */
  public function testNicenumG(): void {
    $this->assertEquals("1.00 G", Util::nicenum(1073741824 + 1));
  }
  
  /**
   * nicenum uses optional threshold and divider (e.g., 1000-based).
   */
  public function testNicenumCustomScale(): void {
    $this->assertEquals("1.00 k", Util::nicenum(1001, 1000, 1000));
  }
  
  /**
   * showperc returns 0.00 for zero total.
   */
  public function testShowpercZeroTotal(): void {
    $this->assertEquals("0.00", Util::showperc(0, 0));
  }
  
  /**
   * showperc returns 100.00 for equal values.
   */
  public function testShowpercFull(): void {
    $this->assertEquals("100.00", Util::showperc(100, 100));
  }
  
  /**
   * showperc clamps below 100% when part < total due to rounding.
   */
  public function testShowpercClampHigh(): void {
    $percent = Util::showperc(99, 100);
    $this->assertNotEquals("100.00", $percent);
    $this->assertEquals("99.00", $percent);
  }
  
  /**
   * showperc clamps above 0% when part > 0 due to rounding.
   */
  public function testShowpercClampLow(): void {
    $percent = Util::showperc(1, 10000);
    $this->assertNotEquals("0.00", $percent);
    $this->assertEquals("0.01", $percent);
  }
  
  /**
   * showperc handles normal values.
   */
  public function testShowpercNormal(): void {
    $this->assertEquals("50.00", Util::showperc(50, 100));
  }
  
  /**
   * showperc uses custom decimal places.
   */
  public function testShowpercCustomDecimals(): void {
    $this->assertEquals("33.333", Util::showperc(1, 3, 3));
  }
  
  /**
   * niceround rounds normally.
   */
  public function testNiceroundNormal(): void {
    $this->assertEquals("3.14", Util::niceround(3.14159, 2));
  }
  
  /**
   * niceround with zero decimals rounds to integer.
   */
  public function testNiceroundZeroDecimals(): void {
    $this->assertEquals("3", Util::niceround(3.14159, 0));
  }
  
  /**
   * niceround pads trailing zeros.
   */
  public function testNiceroundPadZeros(): void {
    $this->assertEquals("5.000", Util::niceround(5, 3));
  }
  
  /**
   * niceround handles non-rounding values that still need padding.
   */
  public function testNiceroundExactDecimal(): void {
    $this->assertEquals("2.5", Util::niceround(2.5, 1));
  }
  
  /**
   * shortenstring returns the full string if shorter than limit.
   */
  public function testShortenstringShort(): void {
    $this->assertEquals("<span title='hello'>hello</span>", Util::shortenstring("hello", 10));
  }
  
  /**
   * shortenstring truncates with ellipsis if longer than limit.
   */
  public function testShortenstringLong(): void {
    $result = Util::shortenstring("hello world this is long", 10);
    $this->assertStringContainsString("...", $result);
    $this->assertStringContainsString("<span", $result);
  }
  
  /**
   * shortenstring returns exact string if length matches limit.
   */
  public function testShortenstringExact(): void {
    $this->assertEquals("<span title='hello'>hello</span>", Util::shortenstring("hello", 5));
  }
  
  /**
   * prefixNum pads with leading zeros.
   */
  public function testPrefixNumPad(): void {
    $this->assertEquals("005", Util::prefixNum(5, 3));
  }
  
  /**
   * prefixNum does not pad when already at length.
   */
  public function testPrefixNumExact(): void {
    $this->assertEquals("100", Util::prefixNum(100, 3));
  }
  
  /**
   * prefixNum does not truncate when longer than size.
   */
  public function testPrefixNumLonger(): void {
    $this->assertEquals("1000", Util::prefixNum(1000, 3));
  }
  
  /**
   * prefixNum pads zero.
   */
  public function testPrefixNumZero(): void {
    $this->assertEquals("000", Util::prefixNum(0, 3));
  }
  
  /**
   * strToHex converts a string to hex.
   */
  public function testStrToHex(): void {
    $this->assertEquals("48656c6c6f", Util::strToHex("Hello"));
  }
  
  /**
   * strToHex returns empty string for empty input.
   */
  public function testStrToHexEmpty(): void {
    $this->assertEquals("", Util::strToHex(""));
  }
  
  /**
   * hextobin converts hex back to binary string.
   */
  public function testHextobin(): void {
    $this->assertEquals("Hello", Util::hextobin("48656c6c6f"));
  }
  
  /**
   * hextobin handles odd-length hex strings by skipping the last character.
   */
  public function testHextobinOddLength(): void {
    $this->assertEquals("\x48\x65", Util::hextobin("4865l"));
  }
  
  /**
   * hextobin returns empty for empty input.
   */
  public function testHextobinEmpty(): void {
    $this->assertEquals("", Util::hextobin(""));
  }
  
  /**
   * startsWith returns true for an exact prefix match.
   */
  public function testStartsWithExact(): void {
    $this->assertTrue(Util::startsWith("hello world", "hello"));
  }
  
  /**
   * startsWith returns false when the pattern is not at the start.
   */
  public function testStartsWithNoMatch(): void {
    $this->assertFalse(Util::startsWith("hello world", "world"));
  }
  
  /**
   * startsWith returns true for an empty pattern.
   */
  public function testStartsWithEmptyPattern(): void {
    $this->assertTrue(Util::startsWith("hello", ""));
  }
  
  /**
   * startsWith is case-sensitive.
   */
  public function testStartsWithCaseSensitive(): void {
    $this->assertFalse(Util::startsWith("Hello", "hello"));
  }
  
  /**
   * endsWith returns true for an exact suffix match.
   */
  public function testEndsWithExact(): void {
    $this->assertTrue(Util::endsWith("hello world", "world"));
  }
  
  /**
   * endsWith returns false when the pattern is not at the end.
   */
  public function testEndsWithNoMatch(): void {
    $this->assertFalse(Util::endsWith("hello world", "hello"));
  }
  
  /**
   * endsWith returns true for an empty pattern.
   */
  public function testEndsWithEmptyPattern(): void {
    $this->assertTrue(Util::endsWith("hello", ""));
  }
  
  /**
   * endsWith is case-sensitive.
   */
  public function testEndsWithCaseSensitive(): void {
    $this->assertFalse(Util::endsWith("Hello", "hello"));
  }
  
  /**
   * getMinorVersion extracts major.minor from a full semver string.
   */
  public function testGetMinorVersion(): void {
    $this->assertEquals("1.2", Util::getMinorVersion("1.2.3"));
  }
  
  /**
   * getMinorVersion handles two-part versions.
   */
  public function testGetMinorVersionTwoParts(): void {
    $this->assertEquals("1.0", Util::getMinorVersion("1.0"));
  }
  
  /**
   * getMinorVersion handles larger numbers.
   */
  public function testGetMinorVersionLarge(): void {
    $this->assertEquals("10.20", Util::getMinorVersion("10.20.30"));
  }
  
  /**
   * randomString generates a string of the requested length.
   */
  public function testRandomStringLength(): void {
    $result = Util::randomString(15);
    $this->assertEquals(15, strlen($result));
  }
  
  /**
   * randomString generates characters only from the given charset.
   */
  public function testRandomStringCharset(): void {
    $charset = "ABC";
    $result = Util::randomString(100, $charset);
    for ($i = 0; $i < strlen($result); $i++) {
      $this->assertStringContainsString($result[$i], $charset);
    }
  }
  
  /**
   * randomString with empty length returns empty string.
   */
  public function testRandomStringZeroLength(): void {
    $this->assertEquals("", Util::randomString(0));
  }
  
  /**
   * randomString with default charset uses alphanumeric characters.
   */
  public function testRandomStringDefaultCharset(): void {
    $result = Util::randomString(50);
    $this->assertMatchesRegularExpression('/^[a-zA-Z0-9]+$/', $result);
  }
  
  /**
   * compressDevices returns an empty array for empty input.
   */
  public function testCompressDevicesEmpty(): void {
    $this->assertSame([], Util::compressDevices([]));
  }
  
  /**
   * compressDevices replaces known patterns via str_replace (only the matched portion).
   */
  public function testCompressDevicesPatterns(): void {
    $input = [
      "NVIDIA GeForce RTX 3080",
      "Intel(R) Core(TM) i7-10700K CPU @ 3.80GHz",
      "Generic Device"
    ];
    $result = Util::compressDevices($input);
    $this->assertSame(["NVIDIA RTX 3080", "Core i7-10700K  3.80GHz", "Generic Device"], $result);
  }
  
  /**
   * getFileExtension returns .bin for Linux.
   */
  public function testGetFileExtensionLinux(): void {
    $this->assertEquals(".bin", Util::getFileExtension(0));
  }
  
  /**
   * getFileExtension returns .exe for Windows.
   */
  public function testGetFileExtensionWindows(): void {
    $this->assertEquals(".exe", Util::getFileExtension(1));
  }
  
  /**
   * getFileExtension returns .osx for OSX.
   */
  public function testGetFileExtensionOsx(): void {
    $this->assertEquals(".osx", Util::getFileExtension(2));
  }
  
  /**
   * getFileExtension returns empty string for unknown OS.
   */
  public function testGetFileExtensionUnknown(): void {
    $this->assertEquals("", Util::getFileExtension(99));
  }
  
  /**
   * getStaticArray returns the OS icon for each OS enum value.
   */
  public function testGetStaticArrayOs(): void {
    $this->assertStringContainsString("fa-linux", Util::getStaticArray("0", "os"));
    $this->assertStringContainsString("fa-windows", Util::getStaticArray("1", "os"));
    $this->assertStringContainsString("fa-apple", Util::getStaticArray("2", "os"));
  }
  
  /**
   * getStaticArray returns "unknown" for OS with val -1.
   */
  public function testGetStaticArrayOsUnknown(): void {
    $this->assertEquals("unknown", Util::getStaticArray("-1", "os"));
  }
  
  /**
   * getStaticArray returns the state label for each chunk state.
   */
  public function testGetStaticArrayStates(): void {
    $this->assertEquals("New", Util::getStaticArray("0", "states"));
    $this->assertEquals("Running", Util::getStaticArray("2", "states"));
    $this->assertEquals("Cracked", Util::getStaticArray("5", "states"));
  }
  
  /**
   * getStaticArray returns the format label for each format enum.
   */
  public function testGetStaticArrayFormats(): void {
    $this->assertEquals("Text", Util::getStaticArray("0", "formats"));
    $this->assertEquals("Superhashlist", Util::getStaticArray("3", "formats"));
  }
  
  /**
   * getStaticArray returns the format table name.
   */
  public function testGetStaticArrayFormatTables(): void {
    $this->assertEquals("hashes", Util::getStaticArray("0", "formattables"));
    $this->assertEquals("hashes_binary", Util::getStaticArray("1", "formattables"));
  }
  
  /**
   * getStaticArray returns the platform label.
   */
  public function testGetStaticArrayPlatforms(): void {
    $this->assertEquals("unknown", Util::getStaticArray("-1", "platforms"));
    $this->assertEquals("NVidia", Util::getStaticArray("1", "platforms"));
  }
  
  /**
   * getStaticArray returns empty string for unknown ID.
   */
  public function testGetStaticArrayUnknown(): void {
    $this->assertEquals("", Util::getStaticArray("0", "nonexistent"));
  }
  
  /**
   * updateVersionComparison returns 1 when version2 is newer.
   */
  public function testUpdateVersionComparisonNewer(): void {
    $this->assertEquals(1, Util::updateVersionComparison("update_v0.13.0_v0.14.0", "update_v0.14.0_v0.15.0"));
  }
  
  /**
   * updateVersionComparison returns -1 when version2 is older.
   */
  public function testUpdateVersionComparisonOlder(): void {
    $this->assertEquals(-1, Util::updateVersionComparison("update_v0.14.0_v0.15.0", "update_v0.13.0_v0.14.0"));
  }
  
  /**
   * updateVersionComparison returns 0 for equal versions.
   */
  public function testUpdateVersionComparisonEqual(): void {
    $this->assertEquals(0, Util::updateVersionComparison("update_v0.14.0_v0.15.0", "update_v0.14.0_v0.15.0"));
  }
  
  /**
   * updateVersionComparison treats an invalid prefix as older than a valid one.
   */
  public function testUpdateVersionComparisonInvalidPrefix(): void {
    $this->assertEquals(1, Util::updateVersionComparison("invalid_v0.14.0_v0.15.0", "update_v0.14.0_v0.15.0"));
  }
  
  /**
   * updateVersionComparison handles single-digit version components.
   */
  public function testUpdateVersionComparisonSingleDigit(): void {
    $this->assertEquals(1, Util::updateVersionComparison("update_v1.0.0_v1.1.0", "update_v1.1.0_v2.0.0"));
  }
  
  /**
   * updateVersionComparison extracts and compares versions with +dev build metadata.
   * Composer's semver library considers 1.0.0 > 1.0.0+dev, so the release version
   * sorts after the +dev build.
   */
  public function testUpdateVersionComparisonWithDevBuildMetadata(): void {
    $this->assertEquals(1, Util::updateVersionComparison("update_v1.0.0+dev_v1.1.0", "update_v1.0.0_v1.1.0"));
  }
  
  /**
   * updateVersionComparison treats a +dev migration as older than the next release.
   * 1.0.0+dev < 1.1.0 (build metadata ignored, then minor bump).
   */
  public function testUpdateVersionComparisonDevIsOlderThanNext(): void {
    $this->assertEquals(1, Util::updateVersionComparison("update_v1.0.0+dev_v1.0.1", "update_v1.0.1_v1.1.0"));
  }
  
  /**
   * updateVersionComparison treats a pre-release (e.g. -rc1) as older than the final release.
   * Per semver: 1.0.0-rc1 < 1.0.0.
   */
  public function testUpdateVersionComparisonPrereleaseOlderThanFinal(): void {
    $this->assertEquals(1, Util::updateVersionComparison("update_v1.0.0-rc1_v1.0.0", "update_v1.0.0_v1.0.1"));
  }
  
  /**
   * updateVersionComparison treats an older pre-release as older than a newer pre-release.
   */
  public function testUpdateVersionComparisonPrereleaseNewer(): void {
    $this->assertEquals(1, Util::updateVersionComparison("update_v1.0.0-beta_v1.0.0-rc1", "update_v1.0.0-rc1_v1.0.0"));
  }
  
  /**
   * updateVersionComparison treats a +dev version as different from a -rc pre-release
   * even when the numeric portion is the same (dev < rc in semver convention).
   */
  public function testUpdateVersionComparisonDevVsPrerelease(): void {
    $this->assertEquals(1, Util::updateVersionComparison("update_v1.0.0+dev_v1.0.0-rc1", "update_v1.0.0-rc1_v1.0.0"));
  }
  
  /**
   * arrayOfIds extracts IDs from an array of models created in the database.
   */
  public function testArrayOfIds(): void {
    $ag1 = $this->createAccessGroup('ids_test');
    $ag2 = $this->createAccessGroup('ids_test');
    $result = Util::arrayOfIds([$ag1, $ag2]);
    $this->assertCount(2, $result);
    $this->assertContains($ag1->getId(), $result);
    $this->assertContains($ag2->getId(), $result);
  }
  
  /**
   * arrayOfIds returns an empty array for an empty input.
   */
  public function testArrayOfIdsEmpty(): void {
    $this->assertSame([], Util::arrayOfIds([]));
  }
  
  /**
   * calculate returns the input unchanged (identity function).
   */
  public function testCalculate(): void {
    $this->assertSame(42, Util::calculate(42));
    $this->assertSame("hello", Util::calculate("hello"));
    $this->assertSame(null, Util::calculate(null));
    $this->assertSame([1, 2, 3], Util::calculate([1, 2, 3]));
  }
  
  /**
   * getHashtypeById returns the description for an existing hashtype.
   */
  public function testGetHashtypeById(): void {
    $hashtype = $this->createHashType();
    $result = Util::getHashtypeById($hashtype->getId());
    $this->assertEquals($hashtype->getDescription(), $result);
  }
  
  /**
   * getHashtypeById returns "N/A" for a non-existent ID.
   */
  public function testGetHashtypeByIdNotFound(): void {
    $this->assertEquals("N/A", Util::getHashtypeById(99999999));
  }
  
  /**
   * getUsernameById returns the username for an existing user.
   */
  public function testGetUsernameById(): void {
    $user = $this->createUser('util_test');
    $result = Util::getUsernameById($user->getId());
    $this->assertEquals($user->getUsername(), $result);
  }
  
  /**
   * getUsernameById returns just the ID (with dash prefix) for a non-existent ID
   * due to ternary operator precedence: "Unknown" . (strlen($id) > 0) is evaluated
   * first (truthy string), then the ternary returns "-$id".
   */
  public function testGetUsernameByIdNotFound(): void {
    $result = Util::getUsernameById(99999999);
    $this->assertEquals("-99999999", $result);
  }
  
  /**
   * checkDataDirectory creates a new StoredValue if the key does not exist.
   *
   * @throws Exception
   */
  public function testCheckDataDirectoryCreatesNew(): void {
    $key = 'test_dir_' . uniqid();
    $dir = '/tmp/test_hashtopolis';
    Util::checkDataDirectory($key, $dir);
    $stored = Factory::getStoredValueFactory()->get($key);
    $this->assertNotNull($stored);
    $this->assertEquals($dir, $stored->getVal());
    Factory::getStoredValueFactory()->delete($stored);
  }
  
  /**
   * checkDataDirectory updates an existing StoredValue if the value changed.
   *
   * @throws Exception
   */
  public function testCheckDataDirectoryUpdatesOnChange(): void {
    $key = 'test_dir_upd_' . uniqid();
    $oldDir = '/tmp/test_old';
    Factory::getStoredValueFactory()->save(new StoredValue($key, $oldDir));
    $newDir = '/tmp/test_new';
    Util::checkDataDirectory($key, $newDir);
    $stored = Factory::getStoredValueFactory()->get($key);
    $this->assertEquals($newDir, $stored->getVal());
    Factory::getStoredValueFactory()->delete($stored);
  }
  
  /**
   * checkDataDirectory does not update if the value is already correct.
   *
   * @throws Exception
   */
  public function testCheckDataDirectoryNoChange(): void {
    $key = 'test_dir_stable_' . uniqid();
    $dir = '/tmp/test_stable';
    Factory::getStoredValueFactory()->save(new StoredValue($key, $dir));
    Util::checkDataDirectory($key, $dir);
    $stored = Factory::getStoredValueFactory()->get($key);
    $this->assertEquals($dir, $stored->getVal());
    Factory::getStoredValueFactory()->delete($stored);
  }
}
