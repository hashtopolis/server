<?php

namespace Hashtopolis\inc\apiv2\model;

use Hashtopolis\dba\AbstractModel;
use Hashtopolis\dba\models\CrackerBinary;
use Hashtopolis\dba\models\CrackerBinaryHashtype;
use Hashtopolis\dba\models\HashType;
use Hashtopolis\dba\models\User;
use Hashtopolis\dba\ContainFilter;
use Hashtopolis\dba\ExistsFilter;
use Hashtopolis\dba\Factory;
use Hashtopolis\dba\QueryFilter;
use Hashtopolis\inc\apiv2\common\AbstractModelAPI;
use Hashtopolis\inc\apiv2\error\HttpError;
use Hashtopolis\inc\apiv2\error\ResourceNotFoundError;
use Hashtopolis\inc\utils\AccessUtils;
use Hashtopolis\inc\utils\HashtypeUtils;
use Hashtopolis\inc\HTException;
use Hashtopolis\inc\Util;
use Exception;


/**
 * @extends AbstractModelAPI<HashType>
 */
class HashTypeAPI extends AbstractModelAPI {
  public static function getBaseUri(): string {
    return "/api/v2/ui/hashtypes";
  }
  
  public static function getDBAclass(): string {
    return HashType::class;
  }
  
  public static function getToManyRelationships(): array {
    return [
      'crackerBinaries' => [
        'key' => HashType::HASH_TYPE_ID,

        'junctionTableType' => CrackerBinaryHashtype::class,
        'junctionTableFilterField' => CrackerBinaryHashtype::HASH_TYPE_ID,
        'junctionTableJoinField' => CrackerBinaryHashtype::CRACKER_BINARY_ID,

        'relationType' => CrackerBinary::class,
        'relationKey' => CrackerBinary::CRACKER_BINARY_ID,

        'filterACL' => true,

        // the association is edited from the cracker binary side, from the
        // hashtype side it is only visible
        'readonly' => true,
      ],
    ];
  }
  
  /**
   * Hashtypes are only visible through the cracker binaries supporting them:
   * a hashtype is part of the results iff at least one cracker binary of one
   * of the caller's access groups is associated with it. Binaries without any
   * association do not make hashtypes visible, the associations are the only
   * authority. Administrators bypass the check to manage the global hashtype
   * catalog, including hashtypes which are not associated with any binary yet.
   *
   * @throws Exception
   */
  protected function getFilterACL(): array {
    if ($this->isAdminUser()) {
      return [];
    }
    return [
      Factory::FILTER => [
        // an exists filter avoids duplicate hashtypes which an inner join
        // over the associations would return, one per matching binary
        new ExistsFilter(
          Factory::getCrackerBinaryHashtypeFactory(),
          CrackerBinaryHashtype::HASH_TYPE_ID,
          HashType::HASH_TYPE_ID,
          [new ContainFilter(
            CrackerBinaryHashtype::CRACKER_BINARY_ID,
            self::getAccessibleBinaryIds($this->getCurrentUser()),
            Factory::getCrackerBinaryHashtypeFactory()
          )]
        ),
      ],
    ];
  }

  /**
   * @param HashType $object
   * @throws Exception
   */
  protected function getSingleACL(User $user, AbstractModel $object): bool {
    $qF1 = new QueryFilter(CrackerBinaryHashtype::HASH_TYPE_ID, $object->getId(), "=", Factory::getCrackerBinaryHashtypeFactory());
    $qF2 = new ContainFilter(
      CrackerBinaryHashtype::CRACKER_BINARY_ID,
      self::getAccessibleBinaryIds($user),
      Factory::getCrackerBinaryHashtypeFactory()
    );
    $associations = Factory::getCrackerBinaryHashtypeFactory()->filter([Factory::FILTER => [$qF1, $qF2]]);
    return count($associations) > 0;
  }

  /**
   * A hashtype outside the caller's accessible binaries should not be
   * discoverable: respond as if it does not exist instead of revealing it
   * with a forbidden error.
   */
  protected function getSingleACLError(): Exception {
    return new ResourceNotFoundError();
  }

  /**
   * Cracker binaries the given user has access to through their access groups.
   *
   * @return int[] cracker binary ids
   * @throws Exception
   */
  private static function getAccessibleBinaryIds(User $user): array {
    $qF = new ContainFilter(
      CrackerBinary::ACCESS_GROUP_ID,
      Util::arrayOfIds(AccessUtils::getAccessGroupsOfUser($user)),
      Factory::getCrackerBinaryFactory()
    );
    return Util::arrayOfIds(Factory::getCrackerBinaryFactory()->filter([Factory::FILTER => $qF]));
  }

  private function isAdminUser(): bool {
    $group = Factory::getRightGroupFactory()->get($this->getCurrentUser()->getRightGroupId());
    return $group->getPermissions() === 'ALL';
  }

  /**
   * @throws HttpError
   */
  protected function createObject(array $data): int {
    $hashtype = HashtypeUtils::addHashtype(
      $data[HashType::HASH_TYPE_ID],
      $data[HashType::DESCRIPTION],
      $data[HashType::IS_SALTED],
      $data[HashType::IS_SLOW_HASH],
      $this->getCurrentUser()
    );
    
    return $hashtype->getId();
  }
  
  /**
   * @param HashType $object
   * @throws HTException
   */
  protected function deleteObject(AbstractModel $object): void {
    HashtypeUtils::deleteHashtype($object->getId());
  }
}
