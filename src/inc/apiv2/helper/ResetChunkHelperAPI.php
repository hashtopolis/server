<?php

namespace Hashtopolis\inc\apiv2\helper;

use Hashtopolis\dba\models\Chunk;
use Hashtopolis\inc\apiv2\common\AbstractHelperAPI;
use Hashtopolis\inc\HTException;
use Hashtopolis\inc\utils\TaskUtils;

class ResetChunkHelperAPI extends AbstractHelperAPI {
  public static function getBaseUri(): string {
    return "/api/v2/helper/resetChunk";
  }
  
  public static function getAvailableMethods(): array {
    return ['POST'];
  }
  
  public function getRequiredPermissions(string $method): array {
    return [Chunk::PERM_UPDATE];
  }
  
  /**
   * chunkId is the id of the chunk which you want to reset.
   */
  public function getFormFields(): array {
    return [
      Chunk::CHUNK_ID => ['type' => 'int']
    ];
  }
  
  public static function getResponse(): array {
    return ["Reset" => "Success"];
  }
  
  /**
   * Endpoint to reset a chunk.
   * @throws HTException
   */
  public function actionPost(array $data): object|array|null {
    $chunk = self::getChunk($data[Chunk::CHUNK_ID]);
    TaskUtils::resetChunk($chunk->getId(), $this->getCurrentUser());
    return $this->getResponse();
  }
}
