<?php

namespace APIv2;

require_once __DIR__ . '/../../../inc/load.php';
require_once __DIR__ . '/taskDTO.php';

use DBA\AccessGroup,
    DBA\Factory,
    DBA\Hashlist,
    DBA\HashType,
    DBA\Task,
    DBA\TaskWrapper,
    DBA\QueryFilter;

class Mapping {

    public static function getAccessGroup(AccessGroup $accessGroup) {
        $dto = new GetAccessGroup();
        $dto->id = $accessGroup->getId();
        $dto->name = $accessGroup->getGroupName();
        return $dto;
    }

    public static function getHashlist(Hashlist $hashlist) {
        $dto = new GetHashlist();
        $dto->id = $hashlist->getId();
        $dto->name = $hashlist->getHashlistName();
        $dto->format = $hashlist->getFormat();
        $dto->hashtypeId = $hashlist->getHashTypeId();
        $dto->saltSeparator = $hashlist->getSaltSeparator();
        $dto->isSecret = boolval($hashlist->getIsSecret());
        $dto->isHexSalted = boolval($hashlist->getHexSalt());
        $dto->isSalted = boolval($hashlist->getIsSalted());
        $dto->accessGroupId = $hashlist->getAccessGroupId();
        $dto->useBrain = boolval($hashlist->getBrainId());
        $dto->brainFeatures = $hashlist->getBrainFeatures();
        $dto->hashCount = $hashlist->getHashCount();
        $dto->crackedCount = $hashlist->getCracked();
        $dto->notes = $hashlist->getNotes();
        return $dto;
    }

    public static function getHashType(HashType $hashType) {
        $dto = new GetHashType();
        $dto->id = $hashType->getId();
        $dto->description = $hashType->getDescription();
        $dto->isSalted = boolval($hashType->getIsSalted());
        $dto->isSlowHash = boolval($hashType->getIsSlowHash());
        return $dto;
    }

    public static function getSuperTask(TaskWrapper $taskWrapper) {
        $dto = new GetSuperTask();
        $dto->id = $taskWrapper->getId();
        $dto->name = $taskWrapper->getTaskWrapperName();
        $dto->priority = $taskWrapper->getPriority();
        $dto->crackedCount = $taskWrapper->getCracked();
        $dto->hashlistId = $taskWrapper->getHashlistId();
        $dto->accessGroupId = $taskWrapper->getAccessGroupId();
        $dto->isArchived = boolval($taskWrapper->getIsArchived());        
        return $dto;
    }

    public static function getNormalTask(TaskWrapper $taskWrapper) {
        $qF = new QueryFilter(Task::TASK_WRAPPER_ID, $taskWrapper->getId(), "=");
        $task = Factory::getTaskFactory()->filter([Factory::FILTER => $qF], true);

        $dto = new GetNormalTask();
        $dto->id = $task->getId();
        $dto->name = $task->getTaskName();
        $dto->priority = $task->getPriority();
        $dto->maxAgents = $task->getMaxAgents();
        $dto->hashlistId = $taskWrapper->getHashlistId();

        return $dto;
    }
}
