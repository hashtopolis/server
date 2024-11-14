<?php
use DBA\Chunk;
use DBA\Factory;

require_once(dirname(__FILE__) . "/../common/AbstractHelperAPI.class.php");

class ChunkResetHelperAPI extends AbstractHelperAPI {
  public static function getBaseUri(): string {
    return "/api/v2/helper/resetChunk";
  }

  public static function getAvailableMethods(): array {
    return ['POST'];
  }

  public function getRequiredPermissions(string $method): array
  {
    return [Chunk::PERM_UPDATE];
  }

  public function getFormFields(): array {
    return  [ 
      Chunk::CHUNK_ID => ['type' => 'int']
    ];
  }

  public function actionPost(array $data): object|array|null {
    $chunk = self::getChunk($data[Chunk::CHUNK_ID]);
    TaskUtils::resetChunk($chunk->getId(), $this->getCurrentUser());
    return null;
  }  
}

ChunkResetHelperAPI::register($app);