<?php

namespace APIv2;

require_once __DIR__ . '/../../../inc/load.php';
require_once __DIR__ . '/api.php';
require_once __DIR__ . '/../dto/hashlistDTO.php';
require_once __DIR__ . '/../dto/mapping.php';
require_once __DIR__ . '/../util/statusCode.php';

use Luracast\Restler\RestException;

use DHashlistFormat,
    HashlistUtils,
    HTException;

use DBA\Factory,
    DBA\Hashlist;

class HashlistController extends ApiController {

  /**
   * Creates a new hashlist.
   *
   * When dataSourceType is 'upload', use multipart/form-data for uploading the file.
   * 
   * @url POST
   * @access protected
   *
   * @param CreateHashlist {@from body}
   *        the properties for the hashlist to create
   *
   * @return int the id of the newly created hash list
   * @throws RestException
   */
  public function createHashlist(CreateHashlist $hashlist) {
    $post = match ($hashlist->dataSourceType) {
      'paste'  => ['hashfield' => base64_decode($hashlist->dataSource)],
      'import' => ['importfile' => $hashlist->dataSource],
      'url'    => ['url' => $hashlist->dataSource],
      'upload' => []
    };
    $files = $_FILES;

    try {
      $result = HashlistUtils::createHashlist(
        $hashlist->name,
        $hashlist->isSalted,
        $hashlist->isSecret,
        $hashlist->isSecret && $hashlist->isHexSalted,
        $hashlist->saltSeparator,
        $hashlist->format,
        $hashlist->hashtypeId,
        $hashlist->saltSeparator,
        $hashlist->accessGroupId,
        $hashlist->dataSourceType,
        $post,
        $files,
        $this->_user(),
        $hashlist->useBrain,
        $hashlist->brainFeatures
      );
      return $result->getId();
    } catch (HTException $e) {
      throw new RestException(StatusCode::BAD_REQUEST, $e->getMessage(), previous: $e);
    }
  }

  /**
   * Gets all hashlists which are accessible.
   * 
   * @url GET
   * @access protected
   * 
   * @return array {@type GetHashlist} 
   */
  public function getHashlists() {
    return array_map(fn($hashlist) => Mapping::getHashlist($hashlist), HashlistUtils::getHashlists($this->_user()));
  }

  /**
   * Gets the hashlist with given id, if accessible.
   * 
   * @url GET {id}
   * @access protected
   * 
   * @param $id {@from path} {@type int}
   *        id of the hashlist to get
   * 
   * @return GetHashlist
   * @throws RestException
   */
  public function getHashlist(int $id) {
    return Mapping::getHashlist($this->_findSingleHashlist($id));
  }

  // TODO: or return the new object
  /**
   * Updates an existing hashlist, if accessible.
   * 
   * @url PATCH {id}
   * @access protected
   * 
   * @param $id {@from path} {@type int}
   *        id of the hash list to patch
   * @param UpdateHashlist {@from body} 
   *        the properties to update
   * 
   * @status 204
   * @throws RestException
   */
  public function updateHashlist(int $id, UpdateHashlist $update) {
    $hashlist = $this->_findSingleHashlist($id);
    try {
      // TODO: create single 'atomic' update function in HashlistUtils?
      if ($update->name != null) {
        HashlistUtils::rename($hashlist->getId(), $update->name, $this->_user());
      }
      if ($update->accessGroupId != null) {
        HashlistUtils::changeAccessGroup($hashlist->getId(), $update->accessGroupId, $this->_user());
      }
      if ($update->notes != null) {
        HashlistUtils::editNotes($hashlist->getId(), $update->notes, $this->_user());
      }
    } catch (HTException $e) {
      throw new RestException(StatusCode::BAD_REQUEST, $e->getMessage(), previous: $e);
    }
  }

  /**
   * Removes the hashlist with given id, if accessible.
   * 
   * @url DELETE {id}
   * @access protected
   * 
   * @param $id {@from path} {@type int}
   *        id of the hash list to delete
   *
   * @status 204
   * @throws RestException
   */
  public function deleteHashlist(int $id) {
    $hashlist = $this->_findSingleHashlist($id);
    try {
      HashlistUtils::delete($hashlist->getId(), $this->_user());
    } catch (HTException $e) {
      throw new RestException(StatusCode::BAD_REQUEST, $e->getMessage(), previous: $e);
    }
  }

  /**
   * Finds the single hashlist with given id.
   * 
   * @return Hashlist the hashlist
   * @throws RestException if the hashlist is not found, or if it is not a single hashlist
   */
  private function _findSingleHashlist($id) {
    $hashlist = Factory::getHashlistFactory()->get($id);
    if ($hashlist == null) {
      throw new RestException(StatusCode::NOT_FOUND, 'hashlist not found for id: ' . $id);
    }
    if ($hashlist->getFormat() == DHashlistFormat::SUPERHASHLIST) {
      throw new RestException(StatusCode::BAD_REQUEST, 'not a single hashlist');
    }
    return $hashlist;
  }
}