-- Broken task handling (issue #884): when several distinct agents fail on the
-- same task, the task is marked broken instead of the reporting agents being
-- deactivated. Nodes are untrusted (a cracking contest), so one node failing is
-- treated as a driver problem on that node, not proof the task is broken. This
-- table records the broken tasks; a task carries at most one row.
CREATE TABLE IF NOT EXISTS brokentask (
    brokentaskid SERIAL PRIMARY KEY,
    taskid       integer      NOT NULL REFERENCES task (taskid),
    time         bigint       NOT NULL,
    reason       varchar(256) NOT NULL,
    UNIQUE (taskid)
);

-- Thresholds (Cracking/Tasks section, id 1). See the mysql migration for the
-- meaning of each key.
INSERT INTO config (configsectionid, item, value)
SELECT 1, 'brokenTaskThreshold', '2'
WHERE NOT EXISTS (SELECT 1 FROM config WHERE item = 'brokenTaskThreshold');
INSERT INTO config (configsectionid, item, value)
SELECT 1, 'brokenAgentThreshold', '3'
WHERE NOT EXISTS (SELECT 1 FROM config WHERE item = 'brokenAgentThreshold');
INSERT INTO config (configsectionid, item, value)
SELECT 1, 'brokenErrorWindow', '3600'
WHERE NOT EXISTS (SELECT 1 FROM config WHERE item = 'brokenErrorWindow');
