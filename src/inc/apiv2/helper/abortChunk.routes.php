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
    // Call action
    TaskUtils::abortChunk($object->getId(), $this->getUser());
    return null;
  }  
}

ChunkAbortHelperAPI::register($app);