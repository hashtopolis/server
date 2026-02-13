<?php

namespace Hashtopolis\inc\apiv2\model;

use Hashtopolis\dba\models\Pretask;
use Hashtopolis\dba\models\Supertask;
use Hashtopolis\dba\models\SupertaskPretask;


use Hashtopolis\inc\apiv2\common\AbstractModelAPI;
use Hashtopolis\inc\apiv2\common\error\HttpError;
use Hashtopolis\inc\HTException;
use Psr\Http\Message\ServerRequestInterface as Request;
use Hashtopolis\inc\utils\SupertaskUtils;


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
    $supertask = SupertaskUtils::createSupertask(
      $data[Supertask::SUPERTASK_NAME],
      $this->db2json($this->getFeatures()['pretasks'], $data["pretasks"])
    );
    return $supertask->getId();
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
      $wantedPretasks[] = self::getPretask($pretask["id"]);
    }
    
    // Find out which to add and remove
    $currentPretasks = SupertaskUtils::getPretasksOfSupertask($id);
    $compare_ids = static function ($a, $b) {
      return ($a->getId() - $b->getId());
    };
    
    $toAddPretasks = array_udiff($wantedPretasks, $currentPretasks, $compare_ids);
    $toRemovePretasks = array_udiff($currentPretasks, $wantedPretasks, $compare_ids);
    
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
