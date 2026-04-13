create view TaskWrapperDisplay as select 
    tw.taskWrapperId as taskWrapperId, tw.priority as taskWrapperPriority, tw.maxAgents as TaskWrapperMaxAgents,
    tw.taskType as taskType, tw.hashlistId as hashlistId, tw.accessGroupId as accessGroupId,
    tw.taskWrapperName as taskWrapperName, tw.isArchived as taskWrapperIsArchived, tw.cracked as cracked,
    t.taskId as taskId, t.taskName as taskName, t.attackCmd as attackCmd, t.chunkTime as chunkTime,
    t.statusTimer as statusTimer, t.keyspace as keyspace, t.keyspaceProgress as keyspaceProgress,
    t.priority as taskPriority, t.maxAgents as taskMaxAgents, t.isArchived as taskIsArchived,
    t.crackerBinaryId as crackerBinaryId, t.isSmall as isSmall, t.isCpuTask as isCpuTask,
    t.usePreprocessor as taskUsePreprocessor, 
    CASE WHEN tw.taskType = 0 THEN t.taskName ELSE tw.taskWrapperName END as displayName
FROM TaskWrapper tw LEFT JOIN Task t on tw.taskType = 0 AND t.taskWrapperId = tw.taskWrapperId;