<?php

namespace Hashtopolis\inc\utils;

use Exception;
use Hashtopolis\dba\models\CrackerBinary;
use Hashtopolis\dba\QueryFilter;
use Hashtopolis\dba\ContainFilter;
use Hashtopolis\dba\Factory;
use Hashtopolis\dba\models\User;
use Composer\Semver\Comparator;
use Hashtopolis\inc\HTException;
use Hashtopolis\inc\Util;

class CrackerBinaryUtils {
  /**
   * Returns the newest version of a cracker binary type. When a user is given, only
   * binaries of access groups the user is a member of are considered.
   *
   * @param int $crackerBinaryTypeId
   * @param User|null $user
   * @return CrackerBinary|null
   * @throws HTException
   * @throws Exception
   */
  public static function getNewestVersion(int $crackerBinaryTypeId, ?User $user = null): ?CrackerBinary {
    $qFs = [new QueryFilter(CrackerBinary::CRACKER_BINARY_TYPE_ID, $crackerBinaryTypeId, "=")];
    if ($user !== null) {
      // only binaries of access groups the user is a member of can be used
      $qFs[] = new ContainFilter(CrackerBinary::ACCESS_GROUP_ID, Util::arrayOfIds(AccessUtils::getAccessGroupsOfUser($user)));
    }
    $binaries = Factory::getCrackerBinaryFactory()->filter([Factory::FILTER => $qFs]);
    /** @var ?CrackerBinary $newest */
    $newest = null;
    foreach ($binaries as $binary) {
      if ($newest == null || Comparator::greaterThan($binary->getVersion(), $newest->getVersion())) {
        $newest = $binary;
      }
    }
    if ($newest == null) {
      throw new HTException("No binary versions available, cannot create tasks!");
    }
    return $newest;
  }
}
