<?php

use DBA\CrackerBinary;
use DBA\QueryFilter;
use DBA\Factory;

class CrackerBinaryUtils {
  /**
   * @param int $crackerBinaryTypeId
   * @return DBA\CrackerBinary|null
   * @throws HTException
   */
  public static function getNewestVersion($crackerBinaryTypeId) {
    $qF = new QueryFilter(CrackerBinary::CRACKER_BINARY_TYPE_ID, $crackerBinaryTypeId, "=");
    $binaries = Factory::getCrackerBinaryFactory()->filter([Factory::FILTER => $qF]);
    /** @var $newest CrackerBinary */
    $newest = null;
    foreach ($binaries as $binary) {
      if ($newest == null || Util::versionComparison($binary->getVersion(), $newest->getVersion()) < 0) {
        $newest = $binary;
      }
    }
    if ($newest == null) {
      throw new HTException("No binary versions available, cannot create tasks!");
    }
    return $newest;
  }
}