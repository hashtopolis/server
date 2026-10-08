<?php

namespace Hashtopolis\inc\apiv2\model;

use Exception;
use Hashtopolis\dba\AbstractModel;
use Hashtopolis\dba\Factory;
use Hashtopolis\dba\models\Pretask;
use Hashtopolis\dba\models\Supertask;
use Hashtopolis\dba\models\SupertaskPretask;
use Hashtopolis\dba\QueryFilter;
use Hashtopolis\inc\apiv2\common\AbstractModelAPI;
use Hashtopolis\inc\apiv2\error\HttpConflict;
use Hashtopolis\inc\apiv2\error\HttpError;
use Hashtopolis\inc\apiv2\error\HttpForbidden;
use Hashtopolis\inc\apiv2\error\InternalError;
use Hashtopolis\inc\apiv2\error\ResourceNotFoundError;
use Hashtopolis\inc\HTException;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Hashtopolis\inc\utils\SupertaskUtils;


/**
 * @extends AbstractModelAPI<Supertask>
 */
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
      "pretasks" => ['type' => 'array', 'subtype' => 'int'],
      "crackerBinaryTypeId" => ['type' => 'int']
    ];
  }
  
  /**
   * @throws HttpError
   */
  protected function createObject(array $data): int {
    /* Use quirk on 'pretasks' since this is casted to DB representation  */
    $supertask = SupertaskUtils::createSupertask(
      $data[Supertask::SUPERTASK_NAME],
      $this->db2json($this->getFeatures()['pretasks'], $data["pretasks"]),
      (int)$data["crackerBinaryTypeId"]
    );
    return $supertask->getId();
  }
  
  /**
   * Replaces the pretasks of the supertask with the given list, i.e. pretasks
   * given in the list but not associated yet are added and associations which
   * are not in the list are removed. The complete resulting list is validated
   * before anything is changed, the pretasks of a supertask must all be of the
   * same cracker binary type and a supertask must contain at least one pretask.
   *
   * @param Request $request
   * @param array $data
   * @param array $args
   * @throws HTException
   * @throws HttpError
   * @throws ResourceNotFoundError
   * @throws Exception
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
    if (sizeof($wantedPretasks) == 0) {
      throw new HttpError("Cannot update supertask ($id) to have no pretasks, a supertask must contain at least one pretask!");
    }
    SupertaskUtils::checkPretaskBinaryTypeConsistency($wantedPretasks);

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
    // The resulting pretask list was validated as a whole above, so the single
    // pretask consistency checks of the utils are skipped here.
    foreach ($toAddPretasks as $pretask) {
      SupertaskUtils::addPretaskToSupertask($id, $pretask->getId(), false);
    }
    foreach ($toRemovePretasks as $pretask) {
      SupertaskUtils::removePretaskFromSupertask($id, $pretask->getId(), false);
    }
    
    if (!$factory->getDB()->commit()) {
      throw new HttpError("Was not able to update to many relationship");
    }
  }

  /**
   * Pretasks of a supertask must all be of the same cracker binary type, so
   * adding a pretask of a different type than the ones already associated is
   * rejected.
   *
   * @param Request $request
   * @param Response $response
   * @param array $args
   * @throws HTException
   * @throws HttpError
   * @throws HttpForbidden
   * @throws HttpConflict
   * @throws InternalError
   * @throws ResourceNotFoundError
   * @throws Exception
   */
  public function postToManyRelationshipLink(Request $request, Response $response, array $args): Response {
    if ($args['relation'] == 'pretasks') {
      $jsonBody = $request->getParsedBody();
      if ($jsonBody !== null && array_key_exists('data', $jsonBody) && is_array($jsonBody['data']) && sizeof($jsonBody['data']) > 0) {
        // boot the request first (preCommon), then fetch through doFetch, so
        // the request is authorized and the ACL is enforced before validation
        $this->preCommon($request);
        $id = (int)$args['id'];
        $this->doFetch($id);
        $pretasks = SupertaskUtils::getPretasksOfSupertask($id);
        foreach ($jsonBody['data'] as $pretask) {
          if (!$this->validateResourceRecord($pretask)) {
            $encoded_pretask = json_encode($pretask);
            throw new HttpError('Invalid resource record given in list! invalid resource record: ' . $encoded_pretask);
          }
          $pretasks[] = self::getPretask($pretask["id"]);
        }
        SupertaskUtils::checkPretaskBinaryTypeConsistency($pretasks);
      }
    }
    return parent::postToManyRelationshipLink($request, $response, $args);
  }

  /**
   * A supertask must contain at least one pretask, so deleting all of its
   * pretasks in one request is rejected.
   *
   * @param Request $request
   * @param Response $response
   * @param array $args
   * @throws HTException
   * @throws HttpError
   * @throws HttpForbidden
   * @throws InternalError
   * @throws ResourceNotFoundError
   * @throws Exception
   */
  public function deleteToManyRelationshipLink(Request $request, Response $response, array $args): Response {
    if ($args['relation'] == 'pretasks') {
      $jsonBody = $request->getParsedBody();
      if ($jsonBody !== null && array_key_exists('data', $jsonBody) && is_array($jsonBody['data'])) {
        // boot the request first (preCommon), then fetch through doFetch, so
        // the request is authorized and the ACL is enforced before validation
        $this->preCommon($request);
        $id = (int)$args['id'];
        $this->doFetch($id);
        $pretasksToDelete = [];
        foreach ($jsonBody['data'] as $pretask) {
          if (isset($pretask['id']) && is_numeric($pretask['id'])) {
            $pretasksToDelete[] = (int)$pretask['id'];
          }
        }
        $currentPretasks = SupertaskUtils::getPretasksOfSupertask($id);
        $deleteAllPretasks = sizeof($currentPretasks) > 0;
        foreach ($currentPretasks as $pretask) {
          if (!in_array($pretask->getId(), $pretasksToDelete, true)) {
            $deleteAllPretasks = false;
            break;
          }
        }
        if ($deleteAllPretasks) {
          throw new HttpError("Cannot delete all pretasks of supertask ($id), a supertask must contain at least one pretask!");
        }
      }
    }
    return parent::deleteToManyRelationshipLink($request, $response, $args);
  }

  public function getAggregateFieldsets(): array {
    return [
      'supertask' => [
        'amountPretasks' => [$this, 'getAggregateAmountPretasks'],
      ]
    ];
  }

  public static function getAggregateFeatures(): array {
    return [
      'amountPretasks' => self::aggregateFeature('int', 'amountPretasks'),
    ];
  }

  /**
   * @param Supertask $object
   * @throws Exception
   */
  protected function getAggregateAmountPretasks(AbstractModel $object): int {
    $qF = new QueryFilter(SupertaskPretask::SUPERTASK_ID, $object->getId(), "=", Factory::getSupertaskPretaskFactory());
    return Factory::getSupertaskPretaskFactory()->countFilter([Factory::FILTER => $qF]);
  }

  /**
   * @param Supertask $object
   * @throws HTException
   */
  protected function deleteObject(AbstractModel $object): void {
    SupertaskUtils::deleteSupertask($object->getId());
  }
}
