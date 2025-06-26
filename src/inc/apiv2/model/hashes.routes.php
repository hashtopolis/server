<?php

use DBA\ContainFilter;
use DBA\Factory;

use DBA\Chunk;
use DBA\Hash;
use DBA\Hashlist;
use DBA\JoinFilter;
use DBA\User;
use JetBrains\PhpStorm\NoReturn;

require_once(dirname(__FILE__) . "/../common/AbstractModelAPI.class.php");


class HashAPI extends AbstractModelAPI {
    public static function getBaseUri(): string {
      return "/api/v2/ui/hashes";
    }

    public static function getAvailableMethods(): array {
      return ['GET'];
    }

    public static function getDBAclass(): string {
      return Hash::class;
    }
  
    protected function getSingleACL(User $user, object $object): bool {
      $accessGroupsUser = Util::arrayOfIds(AccessUtils::getAccessGroupsOfUser($user));
      $hashlist = Factory::getHashlistFactory()->get($object->getHashlistId());
      
      return in_array($hashlist->getAccessGroupId(), $accessGroupsUser);
    }
  
    protected function getFilterACL(): array {
      $accessGroups = Util::arrayOfIds(AccessUtils::getAccessGroupsOfUser($this->getCurrentUser()));
      
      return [
        Factory::JOIN => [
          new JoinFilter(Factory::getHashlistFactory(), Hash::HASHLIST_ID, Hashlist::HASHLIST_ID),
        ],
        Factory::FILTER => [
          new ContainFilter(Hashlist::ACCESS_GROUP_ID, $accessGroups, Factory::getHashlistFactory()),
        ]
      ];
    }
    
    public static function getToOneRelationships(): array {
      return [
        'chunk' => [
          'key' => Hash::CHUNK_ID, 

          'relationType' => Chunk::class,
          'relationKey' => Chunk::CHUNK_ID,
        ],
        'hashlist' => [
          'key' => Hash::HASHLIST_ID, 

          'relationType' => Hashlist::class,
          'relationKey' => Hashlist::HASHLIST_ID,
        ],
      ];
    }

    #[NoReturn] protected function createObject(array $data): int {
      assert(False, "Hashes cannot be created via API");
    }

    #[NoReturn] public function updateObject(int $objectId, array $data): void {
       assert(False, "Hashes cannot be updated via API");
    }

    #[NoReturn] protected function deleteObject(object $object): void {
      assert(False, "Hashes cannot be deleted via API");
    }
}

HashAPI::register($app);