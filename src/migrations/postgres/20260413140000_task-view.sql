CREATE VIEW TaskWrapperDisplay AS SELECT
    tw.taskWrapperId AS taskWrapperId, tw.priority AS taskWrapperPriority, tw.maxAgents AS taskWrapperMaxAgents,
    tw.taskType AS taskType, tw.hashlistId AS hashlistId, tw.accessGroupId AS accessGroupId,
    tw.taskWrapperName AS taskWrapperName, tw.isArchived AS taskWrapperIsArchived, tw.cracked AS cracked,
    t.taskId AS taskId, t.taskName AS taskName, t.attackCmd AS attackCmd, t.chunkTime AS chunkTime,
    t.statusTimer AS statusTimer, t.keyspace AS keyspace, t.keyspaceProgress AS keyspaceProgress,
    t.priority AS taskPriority, t.maxAgents AS taskMaxAgents, t.isArchived AS taskIsArchived,
    t.isSmall AS isSmall, t.isCpuTask AS isCpuTask, t.usePreprocessor AS taskUsePreprocessor,
    CASE WHEN tw.taskType = 0 THEN t.taskName ELSE tw.taskWrapperName END AS displayName
FROM TaskWrapper tw LEFT JOIN Task t ON tw.taskType = 0 AND t.taskWrapperId = tw.taskWrapperId;
