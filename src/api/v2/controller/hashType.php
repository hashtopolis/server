<?php

namespace APIv2;

require_once __DIR__ . '/../../../inc/load.php';
require_once __DIR__ . '/api.php';
require_once __DIR__ . '/../dto/hashTypeDTO.php';
require_once __DIR__ . '/../dto/mapping.php';

use Luracast\Restler\RestException;

use DBA\Factory;

class HashTypeController extends ApiController {

  /**
   * Gets all hash types.
   * 
   * @url GET
   * @access public
   * 
   * @return array {@type GetHashType}
   */
  public function getHashTypes() {
    return array_map(fn($hashType) => Mapping::getHashType($hashType), Factory::getHashTypeFactory()->filter([]));
  }

  /**
   * Gets the hash type with given id.
   *
   * @url GET {id}
   * @access public
   * 
   * @param $id {@from path} {@type int}
   *        id of the hash type to get
   *
   * @return GetHashType
   * @throws RestException
   */
  public function getHashType(int $id) {
    $hashType = Factory::getHashTypeFactory()->get($id);
    if ($hashType == null) {
      throw new RestException(StatusCode::NOT_FOUND, 'hash type not found for id: ' . $id);
    }
    return Mapping::getHashType($hashType);
  }
}