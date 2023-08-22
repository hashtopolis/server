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

  public function actionPost($mappedFeatures, $QUERY): array|null {
    $pk = $mappedFeatures[Chunk::CHUNK_ID];
    $object = Factory::getChunkFactory()->get($pk);
    if ($object === null) {
      throw new HTException("Chunk '$pk' not found!", 404);
    }
    TaskUtils::resetChunk($object->getId(), $this->getUser());
    return null;
  }  
}

ChunkResetHelperAPI::register($app);