<?php

namespace Hashtopolis\inc;

use Hashtopolis\dba\Factory;
use Hashtopolis\TestBase;

require_once(dirname(__FILE__) . '/../TestBase.php');

final class StartupDataTest extends TestBase {
  private static ?array $setupJson     = null;
  private static ?array $hashtypesJson = null;
  private static ?array $sqlConfigMap  = null;
  
  private function getSetupJson(): array {
    if (self::$setupJson === null) {
      self::$setupJson = json_decode(
        file_get_contents(__DIR__ . '/../../../src/inc/startup/setup.json'),
        true
      );
    }
    return self::$setupJson;
  }
  
  private function getHashtypesJson(): array {
    if (self::$hashtypesJson === null) {
      $content = file_get_contents(__DIR__ . '/../../../src/inc/startup/hashtypes.json');
      self::$hashtypesJson = json_decode($content, true);
    }
    return self::$hashtypesJson;
  }
  
  /**
   * hacky way of parsing the existing sql and extract the inserts to compare
   * can be removed when we can drop the comparison test, currently we use it to avoid regressions and duplicate
   * entries in case there is a primary key mixup
   *
   * @return array
   */
  private function getSqlConfigMap(): array {
    if (self::$sqlConfigMap !== null) {
      return self::$sqlConfigMap;
    }
    
    $sql = file_get_contents(__DIR__ . '/../../../src/migrations/mysql/20260619090219_initial.sql');
    // Find the INSERT line and extract the values portion between the first ( and the final ); that ends the statement
    preg_match("/^INSERT INTO `Config` VALUES (.+);$/m", $sql, $m);
    $valuesStr = $m[1];
    
    self::$sqlConfigMap = [];
    $len = strlen($valuesStr);
    $i = 0;
    while ($i < $len && $valuesStr[$i] !== '(') {
      $i++;
    }
    while ($i < $len) {
      // Expect '(' at $i
      if ($valuesStr[$i] !== '(') {
        break;
      }
      $i++; // skip '('
      
      // Parse configId
      $configId = '';
      while ($i < $len && ctype_digit($valuesStr[$i])) {
        $configId .= $valuesStr[$i];
        $i++;
      }
      if ($i >= $len || $valuesStr[$i] !== ',') {
        break;
      }
      $i++; // skip ','
      
      // Parse configSectionId
      $configSectionId = '';
      while ($i < $len && ctype_digit($valuesStr[$i])) {
        $configSectionId .= $valuesStr[$i];
        $i++;
      }
      if ($i >= $len || $valuesStr[$i] !== ',') {
        break;
      }
      $i++; // skip ','
      
      // Parse item (single-quoted string with possible escape sequences)
      if ($i >= $len || $valuesStr[$i] !== "'") {
        break;
      }
      $i++; // skip opening '
      $item = '';
      while ($i < $len) {
        if ($valuesStr[$i] === '\\' && $i + 1 < $len) {
          $item .= $valuesStr[$i] . $valuesStr[$i + 1];
          $i += 2;
        }
        elseif ($valuesStr[$i] === "'") {
          $i++; // skip closing '
          break;
        }
        else {
          $item .= $valuesStr[$i];
          $i++;
        }
      }
      
      if ($i >= $len || $valuesStr[$i] !== ',') {
        break;
      }
      $i++; // skip ','
      
      // Parse value (single-quoted string with possible escape sequences)
      if ($i >= $len || $valuesStr[$i] !== "'") {
        break;
      }
      $i++; // skip opening '
      $value = '';
      while ($i < $len) {
        if ($valuesStr[$i] === '\\' && $i + 1 < $len) {
          $value .= $valuesStr[$i] . $valuesStr[$i + 1];
          $i += 2;
        }
        elseif ($valuesStr[$i] === "'") {
          $i++; // skip closing '
          break;
        }
        else {
          $value .= $valuesStr[$i];
          $i++;
        }
      }
      
      if ($i >= $len || $valuesStr[$i] !== ')') {
        break;
      }
      $i++; // skip ')'
      
      $cid = (int)$configId;
      $unquotedItem = stripcslashes($item);
      self::$sqlConfigMap[$unquotedItem] = $cid;
      
      // skip optional ',' separator
      if ($i < $len && $valuesStr[$i] === ',') {
        $i++;
      }
      // skip whitespace
      while ($i < $len && ($valuesStr[$i] === ' ' || $valuesStr[$i] === "\n" || $valuesStr[$i] === "\r" || $valuesStr[$i] === "\t")) {
        $i++;
      }
      // If next is ';', we are done
      if ($i < $len && $valuesStr[$i] === ';') {
        break;
      }
    }
    
    return self::$sqlConfigMap;
  }
  
  /**
   * setup.json is valid JSON.
   */
  public function testSetupJsonIsValid(): void {
    $data = $this->getSetupJson();
    $this->assertIsArray($data);
    $this->assertArrayHasKey('Config', $data);
    $this->assertArrayHasKey('ConfigSection', $data);
  }
  
  /**
   * Each top-level key in setup.json maps to a registered factory's getModelName().
   */
  public function testSetupJsonAllModelNamesMapToFactories(): void {
    $data = $this->getSetupJson();
    $factoryMethods = [
      'Config' => Factory::getConfigFactory(),
      'ConfigSection' => Factory::getConfigSectionFactory(),
      'ApiGroup' => Factory::getApiGroupFactory(),
      'AgentBinary' => Factory::getAgentBinaryFactory(),
      'CrackerBinary' => Factory::getCrackerBinaryFactory(),
      'CrackerBinaryType' => Factory::getCrackerBinaryTypeFactory(),
      'Preprocessor' => Factory::getPreprocessorFactory(),
      'RightGroup' => Factory::getRightGroupFactory(),
    ];
    foreach ($factoryMethods as $modelName => $factory) {
      $this->assertEquals($modelName, $factory->getModelName(), "Factory modelName mismatch for $modelName");
      $this->assertArrayHasKey($modelName, $data, "setup.json missing key $modelName");
    }
  }
  
  /**
   * Every configId in setup.json Config entries matches the configId for the
   * same item in the SQL migration file.
   *
   * NOTE: This test is only needed until we have the next migration and we do not compare to the sql anymore
   */
  public function testSetupJsonConfigIdsMatchSql(): void {
    $data = $this->getSetupJson();
    $sqlMap = $this->getSqlConfigMap();
    
    foreach ($data['Config'] as $entry) {
      $item = $entry['item'];
      $jsonId = $entry['configId'];
      $this->assertArrayHasKey(
        $item, $sqlMap,
        "Item '$item' in setup.json not found in SQL migration"
      );
      $this->assertEquals(
        $sqlMap[$item], $jsonId,
        "configId mismatch for item '$item': JSON has $jsonId, SQL has {$sqlMap[$item]}"
      );
    }
  }
  
  /**
   * Every configSectionId used by Config entries has a matching entry in ConfigSection.
   */
  public function testSetupJsonConfigSectionsComplete(): void {
    $data = $this->getSetupJson();
    $sectionIds = array_map(fn($cs) => $cs['configSectionId'], $data['ConfigSection']);
    $usedIds = array_unique(array_map(fn($c) => $c['configSectionId'], $data['Config']));
    foreach ($usedIds as $usedId) {
      $this->assertContains(
        $usedId, $sectionIds,
        "Config entry references configSectionId $usedId but no ConfigSection with that ID exists"
      );
    }
  }
  
  /**
   * hashtypes.json is valid JSON and contains an array.
   */
  public function testHashtypesJsonIsValid(): void {
    $data = $this->getHashtypesJson();
    $this->assertIsArray($data);
    $this->assertNotEmpty($data);
  }
  
  /**
   * Every entry in hashtypes.json has all required fields.
   */
  public function testHashtypesJsonAllEntriesHaveRequiredFields(): void {
    $data = $this->getHashtypesJson();
    foreach ($data as $i => $entry) {
      $this->assertArrayHasKey('hashTypeId', $entry, "Entry $i missing hashTypeId");
      $this->assertArrayHasKey('description', $entry, "Entry $i missing description");
      $this->assertArrayHasKey('isSalted', $entry, "Entry $i missing isSalted");
      $this->assertArrayHasKey('isSlowHash', $entry, "Entry $i missing isSlowHash");
      $this->assertIsInt($entry['hashTypeId'], "Entry $i hashTypeId is not int");
      $this->assertIsString($entry['description'], "Entry $i description is not string");
      $this->assertIsInt($entry['isSalted'], "Entry $i isSalted is not int");
      $this->assertIsInt($entry['isSlowHash'], "Entry $i isSlowHash is not int");
    }
  }
  
  /**
   * No duplicate hashTypeId values in hashtypes.json.
   */
  public function testHashtypesJsonUniqueIds(): void {
    $data = $this->getHashtypesJson();
    $ids = array_map(fn($e) => $e['hashTypeId'], $data);
    $uniqueIds = array_unique($ids);
    $this->assertCount(
      count($ids), $uniqueIds,
      'Duplicate hashTypeId values found in hashtypes.json'
    );
  }
  
  /**
   * No entry in hashtypes.json has an empty description.
   */
  public function testHashtypesJsonNoEmptyDescriptions(): void {
    $data = $this->getHashtypesJson();
    foreach ($data as $i => $entry) {
      $this->assertNotEmpty(
        trim($entry['description']),
        "Entry $i (hashTypeId {$entry['hashTypeId']}) has an empty description"
      );
    }
  }
}
