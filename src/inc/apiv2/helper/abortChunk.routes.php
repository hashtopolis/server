<?php
use DBA\Chunk;
use DBA\Factory;

require_once(dirname(__FILE__) . "/../common/AbstractHelperAPI.class.php");

class ChunkAbortHelperAPI extends AbstractHelperAPI {
  public static function getBaseUri(): string {
    return "/api/v2/helper/abortChunk";
  }

  public static function getAvailableMethods(): array {
    return ['POST'];
  }

  public function getRequiredPermissions(string $method): array
  {
    return [Chunk::PERM_UPDATE, Chunk::PERM_DELETE];
  }

  /**
   * ChunkID is the ID of the chunk that needs to be aborted.
   */
  public function getFormFields(): array {
    return  [ 
      Chunk::CHUNK_ID => ['type' => 'int']
    ];
  }

  /**
   * Endpoint to stop a running chunk.
   */
  public function actionPost(array $data): object|array|null {
    $chunk = self::getChunk($data[Chunk::CHUNK_ID]);
    
    TaskUtils::abortChunk($chunk->getId(), $this->getCurrentUser());
    return null;
  }  
}

ChunkAbortHelperAPI::register($app);