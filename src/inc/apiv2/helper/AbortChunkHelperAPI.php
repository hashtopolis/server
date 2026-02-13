<?php

namespace Hashtopolis\inc\apiv2\helper;

use Hashtopolis\dba\models\Chunk;
use Hashtopolis\inc\apiv2\common\AbstractHelperAPI;
use Hashtopolis\inc\HTException;
use Hashtopolis\inc\utils\TaskUtils;

class AbortChunkHelperAPI extends AbstractHelperAPI {
  public static function getBaseUri(): string {
    return "/api/v2/helper/abortChunk";
  }
  
  public static function getAvailableMethods(): array {
    return ['POST'];
  }
  
  public function getRequiredPermissions(string $method): array {
    return [Chunk::PERM_UPDATE, Chunk::PERM_DELETE];
  }
  
  /**
   * ChunkID is the ID of the chunk that needs to be aborted.
   */
  public function getFormFields(): array {
    return [
      Chunk::CHUNK_ID => ['type' => 'int']
    ];
  }
  
  public static function getResponse(): array {
    return ["Abort" => "Success"];
  }
  
  /**
   * Endpoint to stop a running chunk.
   * @throws HTException
   */
  public function actionPost(array $data): object|array|null {
    $chunk = self::getChunk($data[Chunk::CHUNK_ID]);
    
    TaskUtils::abortChunk($chunk->getId(), $this->getCurrentUser());
    return self::getResponse();
  }
}