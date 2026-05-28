<?php

namespace Hashtopolis\inc\apiv2\helper;

use Hashtopolis\dba\models\CrackerBinary;
use Hashtopolis\dba\models\Hashlist;
use Hashtopolis\dba\models\Supertask;
use Hashtopolis\dba\models\Task;
use Hashtopolis\dba\models\TaskWrapper;
use Hashtopolis\inc\apiv2\common\AbstractHelperAPI;
use Hashtopolis\inc\HTException;
use Hashtopolis\inc\utils\SupertaskUtils;

class CreateSupertaskHelperAPI extends AbstractHelperAPI {
  /** @var bool whether the caller opted into skipping already-completed pretasks */
  private bool $skipCompletedRequested = false;
  /** @var array<int, array{pretaskId: int, matchingTaskId: int}> pretasks skipped on the last run */
  private array $skippedPretasks = [];

  public static function getBaseUri(): string {
    return "/api/v2/helper/createSupertask";
  }

  public static function getAvailableMethods(): array {
    return ['POST'];
  }

  public function getRequiredPermissions(string $method): array {
    return [TaskWrapper::PERM_CREATE, Task::PERM_CREATE, Supertask::PERM_READ, Hashlist::PERM_READ, CrackerBinary::PERM_READ];
  }

  /**
   * supertaskTemplateId is the the Id of the supertasktemplate of which you want to create a supertask of.
   * hashlistId is the Id of the hashlist that has to be used for the supertask.
   * crackerVersionId is the Id of the crackerversion that is used for the created supertask.
   * skipCompleted (optional, default false) skips any pretask whose equivalent attack has already
   *   been fully exhausted against the hashlist instead of re-instantiating it.
   */
  public function getFormFields(): array {
    return [
      "supertaskTemplateId" => ["type" => "int"],
      Hashlist::HASHLIST_ID => ["type" => "int"],
      "crackerVersionId" => ["type" => "int"],
      "skipCompleted" => ["type" => "bool", "null" => true],
    ];
  }

  public static function getResponse(): string {
    return "TaskWrapper";
  }

  /**
   * When skipCompleted was requested, expose the skipped pretasks under the top-level
   * "meta" member alongside the returned TaskWrapper resource. When it was not requested,
   * nothing is added, keeping the response identical to the previous behavior.
   */
  protected function getExtraMeta(): array {
    if (!$this->skipCompletedRequested) {
      return [];
    }
    return ["skippedPretasks" => $this->skippedPretasks];
  }

  /**
   * Endpoint to create a supertask from a supertask template.
   *
   * When skipCompleted is true, pretasks whose equivalent attack has already been fully
   * exhausted against the target hashlist are skipped. The skipped pretasks are reported
   * under the top-level "meta.skippedPretasks" member (each entry has pretaskId and
   * matchingTaskId). If every pretask is skipped, no TaskWrapper is created and a meta-only
   * response with "taskWrapperId": null is returned.
   * @throws HTException
   */
  public function actionPost($data): object|array|null {
    $supertaskTemplate = self::getSupertask($data["supertaskTemplateId"]);
    $hashlist = self::getHashlist($data[Hashlist::HASHLIST_ID]);
    $crackerBinary = self::getCrackerBinary($data["crackerVersionId"]);

    $this->skipCompletedRequested = (bool)($data["skipCompleted"] ?? false);

    $result = SupertaskUtils::runSupertask(
      $supertaskTemplate->getId(),
      $hashlist->getId(),
      $crackerBinary->getId(),
      $this->skipCompletedRequested
    );
    $this->skippedPretasks = $result["skippedPretasks"];

    /* Every pretask was already completed against this hashlist: no TaskWrapper was created. */
    if ($result["taskWrapper"] === null) {
      return [
        "taskWrapperId" => null,
        "skippedPretasks" => $this->skippedPretasks,
      ];
    }

    return $result["taskWrapper"];
  }
}
