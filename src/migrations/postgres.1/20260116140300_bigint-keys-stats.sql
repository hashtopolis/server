ALTER TABLE AgentStat ALTER COLUMN agentStatId TYPE BIGINT USING agentStatId::bigint;
ALTER SEQUENCE agentstat_agentstatid_seq AS BIGINT;

ALTER TABLE Speed ALTER COLUMN speedId TYPE BIGINT USING speedId::bigint;
ALTER SEQUENCE speed_speedid_seq AS BIGINT;

ALTER TABLE LogEntry ALTER COLUMN logEntryId TYPE BIGINT USING logEntryId::bigint;
ALTER SEQUENCE logentry_logentryid_seq AS BIGINT;
