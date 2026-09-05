-- Create BackgroundJob table for the cron-based background job queue.
CREATE TABLE `BackgroundJob` (
  `backgroundJobId` int NOT NULL AUTO_INCREMENT,
  `jobType` varchar(100) NOT NULL,
  `payload` json NOT NULL,
  `status` int NOT NULL DEFAULT 0,
  `userId` int DEFAULT NULL,
  `createdAt` bigint NOT NULL,
  `startedAt` bigint DEFAULT NULL,
  `finishedAt` bigint DEFAULT NULL,
  `exitCode` int DEFAULT NULL,
  `resultMessage` varchar(1024) DEFAULT NULL,
  PRIMARY KEY (`backgroundJobId`),
  KEY `status` (`status`),
  CONSTRAINT `BackgroundJob_ibfk_1` FOREIGN KEY (`userId`) REFERENCES `htp_User` (`userId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
