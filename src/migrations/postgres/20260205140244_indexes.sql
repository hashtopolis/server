
-- create indexes on foreign keys which are not created automatically on postgres
CREATE INDEX IF NOT EXISTS ApiKey_apiGroupId_idx ON ApiKey(apiGroupId);
CREATE INDEX IF NOT EXISTS ApiKey_userId_idx ON ApiKey(userId);
CREATE INDEX IF NOT EXISTS File_accessGroupId_idx ON File(accessGroupId);
CREATE INDEX IF NOT EXISTS Hashlist_accessGroupId_idx ON Hashlist(accessGroupId);
CREATE INDEX IF NOT EXISTS HealthCheck_crackerBinaryId_idx ON HealthCheck(crackerBinaryId);
CREATE INDEX IF NOT EXISTS HealthCheckAgent_healthCheckId_idx ON HealthCheckAgent(healthCheckId);
CREATE INDEX IF NOT EXISTS HealthCheckAgent_agentId_idx ON healthCheckAgent(agentId);
CREATE INDEX IF NOT EXISTS Pretask_crackerBinaryTypeId_idx ON Pretask(crackerBinaryTypeId);
CREATE INDEX IF NOT EXISTS Task_taskWrapperId_idx ON Task(taskWrapperId);
CREATE INDEX IF NOT EXISTS Task_crackerBinaryTypeId_idx ON Task(crackerBinaryTypeId);
CREATE INDEX IF NOT EXISTS TaskDebugOutput_taskId_idx ON TaskDebugOutput(taskId);

-- create new indexes on some isArchived columns which is used on a lot of queries
CREATE INDEX IF NOT EXISTS Hashlist_isArchived_idx ON Hashlist(isArchived);
CREATE INDEX IF NOT EXISTS Task_isArchived_priority_idx ON Task(isArchived, priority);
DROP INDEX IF EXISTS TaskWrapper_isArchived_idx; -- we drop and replace the single isArchived index with the following composite one
CREATE INDEX IF NOT EXISTS TaskWrapper_isArchived_priority_idx ON TaskWrapper(isArchived, priority);

