<?php

use DBA\Factory;
use DBA\QueryFilter;
use DBA\OrderFilter;

use DBA\Pretask;
use DBA\Supertask;
use DBA\SupertaskPretask;


require_once __DIR__ . '/../common/ErrorHandler.class.php';

use Psr\Http\Message\ServerRequestInterface as Request;

require_once(dirname(__FILE__) . "/../common/AbstractModelAPI.class.php");


class SupertaskAPI extends AbstractModelAPI {
  public static function getBaseUri(): string {
    return "/api/v2/ui/supertasks";
  }
  
  public static function getDBAclass(): string {
    return Supertask::class;
  }
  
  public static function getToManyRelationships(): array {
    return [
      'pretasks' => [
        'key' => Supertask::SUPERTASK_ID,
        
        'junctionTableType' => SupertaskPretask::class,
        'junctionTableFilterField' => SupertaskPretask::SUPERTASK_ID,
        'junctionTableJoinField' => SupertaskPretask::PRETASK_ID,
        
        'relationType' => Pretask::class,
        'relationKey' => Pretask::PRETASK_ID,
      ],
    ];
  }
  
  public function getFormFields(): array {
    return [
      "pretasks" => ['type' => 'array', 'subtype' => 'int']
    ];
  }
  
  protected function createObject(array $data): int {
    /* Use quirk on 'pretasks' since this is casted to DB representation  */
    SupertaskUtils::createSupertask(
      $data[Supertask::SUPERTASK_NAME],
      $this->db2json($this->getFeatures()['pretasks'], $data["pretasks"])
    );
    
    /* On successfully insert, return ID */
    $qFs = [
      new QueryFilter(Supertask::SUPERTASK_NAME, $data[Supertask::SUPERTASK_NAME], '=')
    ];
    
    /* Hackish way to retrieve object since Id is not returned on creation */
    $oF = new OrderFilter(Supertask::SUPERTASK_ID, "DESC");
    $objects = $this->getFactory()->filter([Factory::FILTER => $qFs, Factory::ORDER => $oF]);
    /* No unique properties set on columns, thus multiple entries could exists, pick the latest (DESC ordering used) */
    assert(count($objects) >= 1);
    
    return $objects[0]->getId();
  }
  
  /**
   * @throws HttpError
   * @throws HTException
   */
  public function updateToManyRelationship(Request $request, array $data, array $args): void {
    $id = $args['id'];
    $wantedPretasks = [];
    foreach ($data as $pretask) {
      if (!$this->validateResourceRecord($pretask)) {
        $encoded_pretask = json_encode($pretask);
        throw new HttpError('Invalid resource record given in list! invalid resource record: ' . $encoded_pretask);
      }
      array_push($wantedPretasks, self::getPretask($pretask["id"]));
    }
    
    // Find out which to add and remove
    $currentPretasks = SupertaskUtils::getPretasksOfSupertask($id);
    function compare_ids($a, $b) {
      return ($a->getId() - $b->getId());
    }
    
    $toAddPretasks = array_udiff($wantedPretasks, $currentPretasks, 'compare_ids');
    $toRemovePretasks = array_udiff($currentPretasks, $wantedPretasks, 'compare_ids');
    
    $factory = $this->getFactory();
    $factory->getDB()->beginTransaction(); //start transaction to be able roll back
    
    // Update models
    foreach ($toAddPretasks as $pretask) {
      SupertaskUtils::addPretaskToSupertask($id, $pretask->getId());
    }
    foreach ($toRemovePretasks as $pretask) {
      SupertaskUtils::removePretaskFromSupertask($id, $pretask->getId());
    }
    
    if (!$factory->getDB()->commit()) {
      throw new HttpError("Was not able to update to many relationship");
    }
  }
  
  /**
   * @throws HTException
   */
  protected function deleteObject(object $object): void {
    SupertaskUtils::deleteSupertask($object->getId());
  }
}

SupertaskAPI::register($app);