<?php

use DBA\Factory;
use DBA\QueryFilter;
use DBA\Benchmark;

class BenchmarkUtils {

  /**
  * @param int $benchmarkId
  * @throws HTException
  */
  public static function getBenchmark($benchmarkId) {
    $benchmark = Factory::getBenchmarkFactory()->get($benchmarkId);
    if ($benchmark == null) {
      throw new HTException("Invalid benchmark ID!");
    }
    return $benchmark;
  }

  /**
  * @param int $benchmarkId
  * @throws HTException
  */ 
  public static function delete($benchmarkId) {
    $benchmark = BenchmarkUtils::getBenchmark($benchmarkId);
    if ($benchmark == null) {
      throw new HTException("Invalid benchmark ID!");
    }
    Factory::getBenchmarkFactory()->delete($benchmark);
  }

  /**
  * @param string $attackCmd
  * @throws HTException
  */ 
  static function cleanupAttackParameters($attackCmd) {
    $attackCmd = trim($attackCmd);

    if (strlen($attackCmd) == 0) {
      throw new HTException("Attack command cannot be empty!");
    } else if (strlen($attackCmd) > 256) {
      throw new HTException("Attack command is too long (max 256 characters)!");
    }

    $parameterParseMap = array();
    $parameterParseMap["-m"] = "--hash-type";
    $parameterParseMap["-a"] = "--attack-mode";
    $parameterParseMap["-S"] = "--slow-candidates";
    $parameterParseMap["-i"] = "--increment";

    $parts_cmd = preg_split('/\s+/', $attackCmd);

    //arguments will be added in a associative array so that the value of the arguments can be easily extracted
    //and the array can be sorted on the keys to get 1 format
    $arguments = array();

    $hashlist = SConfig::getInstance()->getVal(DConfig::HASHLIST_ALIAS);
    $attackValue = ""; //this is the dictionary or attack mask

    $i = 0;
    $size = sizeof($parts_cmd);

    for ($i = 0; $i < count($parts_cmd); $i++) {
      $arg = $parts_cmd[$i];
      if ($arg == $hashlist) { //the hashlist is always #HL# so it doesnt have to be parsed
        continue;
      }

      if (!str_starts_with($arg, "-")) {  //if the argument doesnt start with '-', its the attackvalue which is gonna be appended on the end 
        $attackValue = $arg;
        continue;
      }

      $number = null;
      if (!str_contains($arg, "=")) { //if the argument is in format "-a3" the number needs to be split to properly parse
        $number = preg_replace('/[^0-9]/', '', $arg);
        $arg = preg_replace('/[^a-zA-Z\-]/', '', $arg);
      }

      if (array_key_exists($arg, $parameterParseMap)) { //parse to long format
        $arg = $parameterParseMap[$arg];
      }

      if (!empty($number)) { //when an argument is in format -a3 use the number as value and continue to next itteration
        $arguments[$arg] = $number;
        continue;
      }
      //parse longparameter in style:  --remove-timer=30
      if (str_contains($arg, '=')) {
        $longParameterSplit = explode('=', $arg);
        $arguments[$longParameterSplit[0]] = $longParameterSplit[1];
        continue;
      } else if ($i <= ($size - 2)) {  //if the next character doesnt start with '-' it means that its the value of the parameter
        if (!str_starts_with($parts_cmd[$i + 1], "-")) {
          $arguments[$arg] = $parts_cmd[$i + 1];
          $i++;
          continue;
        }
      }
      $arguments[$arg] = null;
    }

    // if (!array_key_exists("--hash-type", $arguments)) { //if --hash-type not in arguments that means hashtype 0 is in use
    //   $arguments["--hash-type"] = "0";
    // }
    if (!array_key_exists("--attack-mode", $arguments)) { //if --hash-type not in arguments that means attackMode 0 is in use
      $arguments["--attack-mode"] = "0";
    }

    ksort($arguments);  //sort the arguments by key

    $cleanAttackCmd = "";

    foreach ($arguments as $key => $value) {
      $cleanAttackCmd = $cleanAttackCmd . " " . $key;
      if ($value != null) {
        $cleanAttackCmd = $cleanAttackCmd . " " . $value;
      }
    }

    $cleanAttackCmd = $hashlist . $cleanAttackCmd . " " . $attackValue;

    return $cleanAttackCmd;
  }

   /**
   * @param string $attackParameters
   * @param int $hardwareGroupId
   * @param int $hashmode
   * @param string $useNewBenchmark
   * @param int $crackerBinaryId
   */
  public static function getBenchmarkByValue($attackParameters, $hardwareGroupId, $hashmode, $useNewBenchmark, $crackerBinaryId) {
    $hardwareGroup = Factory::getHardwareGroupFactory()->get($hardwareGroupId);
    $crackerBinary = Factory::getCrackerBinaryFactory()->get($crackerBinaryId);

    if ($hardwareGroup == null) {
      throw new HTException("Invallid hardwareGroupId!");
    }
    if ($crackerBinary == null) {
      throw new HTException("Invallid crackerBinaryId!");
    }

    $cleanAttackParameter = self::cleanupAttackParameters($attackParameters);

    $qF = new QueryFilter("attackParameters", $cleanAttackParameter, "=");
    $qF2 = new QueryFilter("hardwareGroupId", $hardwareGroup->getId(), "=");
    $qF3 = new QueryFilter("hashMode", $hashmode, "=");

    $benchmarkType = $useNewBenchmark == 1 ? "speed" : "runtime";

    $qF4 = new QueryFilter("benchmarkType", $benchmarkType, "=");
    $qF5 = new QueryFilter("crackerBinaryId", $crackerBinary->getId(), "=");

    $res = Factory::getBenchmarkFactory()->filter([Factory::FILTER => [$qF, $qF2, $qF3, $qF4, $qF5]], true);

    if (isset($res)) {
      if ($res->getTtl() < time()) { // if ttl has been exceeded, remove value and return null

        Factory::getBenchmarkFactory()->delete($res);
        return null;
      }
    }

    return $res;
  }

  /**
  * @param string $attackParamters
  * @param int $hardwareGroupId
  * @param string $benchmarkValue
  * @param int $hashmode
  * @param string $benchmarkType
  * @param int $crackerBinaryId
  * @throws HTException
  */ 
  public static function saveBenchmarkInCache($attackParameters, $hardwareGroupId, $benchmarkValue, $hashmode, $benchmarkType, $crackerBinaryId) {
    $hardwareGroup = Factory::getHardwareGroupFactory()->get($hardwareGroupId);

    if (!isset($hardwareGroup)) {
      return null;
    }

    $cleanAttackParameters = self::cleanupAttackParameters($attackParameters);
    $ttl = SConfig::getInstance()->getVal(DConfig::BENCHMARKCACHE_TTL);

    $qF = new QueryFilter("attackParameters", $cleanAttackParameters, "=");
    $qF2 = new QueryFilter("hardwareGroupId", $hardwareGroupId, "=");

    $foundBenchmark = Factory::getBenchmarkFactory()->filter([Factory::FILTER => [$qF, $qF2]], true);

    if (isset($foundBenchmark)) { //if benchmark already in cache, update the value

      $foundBenchmark->setTtl(time() + $ttl);
      $foundBenchmark->setBenchmarkValue($benchmarkValue);
      $benchmark = Factory::getBenchmarkFactory()->update($foundBenchmark);
    } else {
      $newBenchmark = new Benchmark(null, $benchmarkType, $benchmarkValue, $cleanAttackParameters, $hashmode, $hardwareGroupId, time() + $ttl, $crackerBinaryId);
      $benchmark = Factory::getBenchmarkFactory()->save($newBenchmark);
    }

    return $benchmark;
  }

  //removes all values where the time to live has been exceeded
  public static function refreshCache() {
    $qF = new QueryFilter("ttl", time(), "<");
    Factory::getFileTaskFactory()->massDeletion([Factory::FILTER => $qF]);
  }

  //removes all values in cache
  public static function deleteCache() {
    Factory::getBenchmarkFactory()->massDeletion([]);
  }
}
