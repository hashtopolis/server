-- Broken task handling (issue #884): when several distinct agents fail on the
-- same task, the task is marked broken instead of the reporting agents being
-- deactivated. Nodes are untrusted (a cracking contest), so one node failing is
-- treated as a driver problem on that node, not proof the task is broken. This
-- table records the broken tasks; a task carries at most one row.
CREATE TABLE IF NOT EXISTS `BrokenTask` (
  `brokenTaskId` INT         NOT NULL AUTO_INCREMENT,
  `taskId`       INT         NOT NULL,
  `time`         BIGINT      NOT NULL,
  `reason`       VARCHAR(256) NOT NULL,
  PRIMARY KEY (`brokenTaskId`),
  UNIQUE KEY `BrokenTask_taskId` (`taskId`),
  CONSTRAINT `BrokenTask_ibfk_1` FOREIGN KEY (`taskId`) REFERENCES `Task` (`taskId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Thresholds (Cracking/Tasks section, id 1).
--   brokenTaskThreshold:  number of DISTINCT agents that must fail on a task
--                         before it is marked broken (0 disables marking tasks broken).
--   brokenAgentThreshold: number of distinct tasks an agent must fail on before
--                         the agent is deactivated (0 disables).
--   brokenErrorWindow:    seconds an error stays relevant for these counts
--                         (0 counts the whole history).
INSERT INTO `Config` (`configSectionId`, `item`, `value`)
SELECT 1, 'brokenTaskThreshold', '2'
WHERE NOT EXISTS (SELECT 1 FROM `Config` WHERE `item` = 'brokenTaskThreshold');
INSERT INTO `Config` (`configSectionId`, `item`, `value`)
SELECT 1, 'brokenAgentThreshold', '3'
WHERE NOT EXISTS (SELECT 1 FROM `Config` WHERE `item` = 'brokenAgentThreshold');
INSERT INTO `Config` (`configSectionId`, `item`, `value`)
SELECT 1, 'brokenErrorWindow', '3600'
WHERE NOT EXISTS (SELECT 1 FROM `Config` WHERE `item` = 'brokenErrorWindow');
