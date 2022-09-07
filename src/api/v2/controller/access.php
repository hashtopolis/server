<?php

namespace APIv2;

require_once __DIR__ . '/../../../inc/load.php';
require_once __DIR__ . '/api.php';
require_once __DIR__ . '/../dto/accessGroupDTO.php';
require_once __DIR__ . '/../dto/mapping.php';
require_once __DIR__ . '/../util/statusCode.php';

use Luracast\Restler\RestException;

use DBA\Factory;

use AccessUtils;

class AccessController extends ApiController {

  /**
   * Gets all access groups which are accessible.
   * 
   * @url GET groups
   * @access protected
   * 
   * @return array {@type GetAccessGroup}
   */
  public function getAccessGroups() {
    return array_map(fn($group) => Mapping::getAccessGroup($group), AccessUtils::getAccessGroupsOfUser($this->_user()));
  }

  /**
   * Gets the access group with given id.
   * 
   * @url GET {id}
   * @access protected
   * 
   * @param $id {@from path} {@type int}
   *        id of the access group to get
   * 
   * @return GetAccessGroup
   * @throws RestException
   */
  public function getAccessGroup(int $id) {
    $accessGroup = Factory::getAccessGroupFactory()->get($id);
    if ($accessGroup == null) {
      throw new RestException(StatusCode::NOT_FOUND, 'access group not found for id: ' . $id);
    }
    return Mapping::getAccessGroup($accessGroup);
  }
}