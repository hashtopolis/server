/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `AccessGroup`
--

DROP TABLE IF EXISTS `AccessGroup`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `AccessGroup` (
  `accessGroupId` int NOT NULL AUTO_INCREMENT,
  `groupName` varchar(50) NOT NULL,
  PRIMARY KEY (`accessGroupId`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `AccessGroup`
--

LOCK TABLES `AccessGroup` WRITE;
/*!40000 ALTER TABLE `AccessGroup` DISABLE KEYS */;
INSERT INTO `AccessGroup` VALUES (1,'Default Group');
/*!40000 ALTER TABLE `AccessGroup` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `AccessGroupAgent`
--

DROP TABLE IF EXISTS `AccessGroupAgent`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `AccessGroupAgent` (
  `accessGroupAgentId` int NOT NULL AUTO_INCREMENT,
  `accessGroupId` int NOT NULL,
  `agentId` int NOT NULL,
  PRIMARY KEY (`accessGroupAgentId`),
  KEY `accessGroupId` (`accessGroupId`),
  KEY `agentId` (`agentId`),
  CONSTRAINT `AccessGroupAgent_ibfk_1` FOREIGN KEY (`accessGroupId`) REFERENCES `AccessGroup` (`accessGroupId`),
  CONSTRAINT `AccessGroupAgent_ibfk_2` FOREIGN KEY (`agentId`) REFERENCES `Agent` (`agentId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `AccessGroupAgent`
--

LOCK TABLES `AccessGroupAgent` WRITE;
/*!40000 ALTER TABLE `AccessGroupAgent` DISABLE KEYS */;
/*!40000 ALTER TABLE `AccessGroupAgent` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `AccessGroupUser`
--

DROP TABLE IF EXISTS `AccessGroupUser`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `AccessGroupUser` (
  `accessGroupUserId` int NOT NULL AUTO_INCREMENT,
  `accessGroupId` int NOT NULL,
  `userId` int NOT NULL,
  PRIMARY KEY (`accessGroupUserId`),
  KEY `accessGroupId` (`accessGroupId`),
  KEY `userId` (`userId`),
  CONSTRAINT `AccessGroupUser_ibfk_1` FOREIGN KEY (`accessGroupId`) REFERENCES `AccessGroup` (`accessGroupId`),
  CONSTRAINT `AccessGroupUser_ibfk_2` FOREIGN KEY (`userId`) REFERENCES `htp_User` (`userId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `AccessGroupUser`
--

LOCK TABLES `AccessGroupUser` WRITE;
/*!40000 ALTER TABLE `AccessGroupUser` DISABLE KEYS */;
/*!40000 ALTER TABLE `AccessGroupUser` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `Agent`
--

DROP TABLE IF EXISTS `Agent`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `Agent` (
  `agentId` int NOT NULL AUTO_INCREMENT,
  `agentName` varchar(100) NOT NULL,
  `uid` varchar(100) NOT NULL,
  `os` int NOT NULL,
  `devices` text NOT NULL,
  `cmdPars` text NOT NULL,
  `ignoreErrors` tinyint NOT NULL,
  `isActive` tinyint NOT NULL,
  `isTrusted` tinyint NOT NULL,
  `token` varchar(30) NOT NULL,
  `lastAct` varchar(50) NOT NULL,
  `lastTime` bigint NOT NULL,
  `lastIp` varchar(50) NOT NULL,
  `userId` int DEFAULT NULL,
  `cpuOnly` tinyint NOT NULL,
  `clientSignature` varchar(50) NOT NULL,
  PRIMARY KEY (`agentId`),
  KEY `userId` (`userId`),
  CONSTRAINT `Agent_ibfk_1` FOREIGN KEY (`userId`) REFERENCES `htp_User` (`userId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Agent`
--

LOCK TABLES `Agent` WRITE;
/*!40000 ALTER TABLE `Agent` DISABLE KEYS */;
/*!40000 ALTER TABLE `Agent` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `AgentBinary`
--

DROP TABLE IF EXISTS `AgentBinary`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `AgentBinary` (
  `agentBinaryId` int NOT NULL AUTO_INCREMENT,
  `binaryType` varchar(20) NOT NULL,
  `version` varchar(20) NOT NULL,
  `operatingSystems` varchar(50) NOT NULL,
  `filename` varchar(50) NOT NULL,
  `updateTrack` varchar(20) NOT NULL,
  `updateAvailable` varchar(20) NOT NULL,
  PRIMARY KEY (`agentBinaryId`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `AgentBinary`
--

LOCK TABLES `AgentBinary` WRITE;
/*!40000 ALTER TABLE `AgentBinary` DISABLE KEYS */;
INSERT INTO `AgentBinary` VALUES (1,'python','0.7.4','Windows, Linux, OS X','hashtopolis.zip','stable','');
/*!40000 ALTER TABLE `AgentBinary` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `AgentError`
--

DROP TABLE IF EXISTS `AgentError`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `AgentError` (
  `agentErrorId` int NOT NULL AUTO_INCREMENT,
  `agentId` int NOT NULL,
  `taskId` int DEFAULT NULL,
  `time` bigint NOT NULL,
  `error` text NOT NULL,
  `chunkId` int DEFAULT NULL,
  PRIMARY KEY (`agentErrorId`),
  KEY `agentId` (`agentId`),
  KEY `taskId` (`taskId`),
  CONSTRAINT `AgentError_ibfk_1` FOREIGN KEY (`agentId`) REFERENCES `Agent` (`agentId`),
  CONSTRAINT `AgentError_ibfk_2` FOREIGN KEY (`taskId`) REFERENCES `Task` (`taskId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `AgentError`
--

LOCK TABLES `AgentError` WRITE;
/*!40000 ALTER TABLE `AgentError` DISABLE KEYS */;
/*!40000 ALTER TABLE `AgentError` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `AgentStat`
--

DROP TABLE IF EXISTS `AgentStat`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `AgentStat` (
  `agentStatId` bigint NOT NULL AUTO_INCREMENT,
  `agentId` int NOT NULL,
  `statType` int NOT NULL,
  `time` bigint NOT NULL,
  `value` varchar(128) NOT NULL,
  PRIMARY KEY (`agentStatId`),
  KEY `agentId` (`agentId`),
  CONSTRAINT `AgentStat_ibfk_1` FOREIGN KEY (`agentId`) REFERENCES `Agent` (`agentId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `AgentStat`
--

LOCK TABLES `AgentStat` WRITE;
/*!40000 ALTER TABLE `AgentStat` DISABLE KEYS */;
/*!40000 ALTER TABLE `AgentStat` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `AgentZap`
--

DROP TABLE IF EXISTS `AgentZap`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `AgentZap` (
  `agentZapId` int NOT NULL AUTO_INCREMENT,
  `agentId` int NOT NULL,
  `lastZapId` int DEFAULT NULL,
  PRIMARY KEY (`agentZapId`),
  KEY `agentId` (`agentId`),
  KEY `lastZapId` (`lastZapId`),
  CONSTRAINT `AgentZap_ibfk_1` FOREIGN KEY (`agentId`) REFERENCES `Agent` (`agentId`),
  CONSTRAINT `AgentZap_ibfk_2` FOREIGN KEY (`lastZapId`) REFERENCES `Zap` (`zapId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `AgentZap`
--

LOCK TABLES `AgentZap` WRITE;
/*!40000 ALTER TABLE `AgentZap` DISABLE KEYS */;
/*!40000 ALTER TABLE `AgentZap` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ApiGroup`
--

DROP TABLE IF EXISTS `ApiGroup`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ApiGroup` (
  `apiGroupId` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `permissions` text NOT NULL,
  PRIMARY KEY (`apiGroupId`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ApiGroup`
--

LOCK TABLES `ApiGroup` WRITE;
/*!40000 ALTER TABLE `ApiGroup` DISABLE KEYS */;
INSERT INTO `ApiGroup` VALUES (1,'Administrators','ALL');
/*!40000 ALTER TABLE `ApiGroup` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ApiKey`
--

DROP TABLE IF EXISTS `ApiKey`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ApiKey` (
  `apiKeyId` int NOT NULL AUTO_INCREMENT,
  `startValid` bigint NOT NULL,
  `endValid` bigint NOT NULL,
  `accessKey` varchar(256) NOT NULL,
  `accessCount` int NOT NULL,
  `userId` int NOT NULL,
  `apiGroupId` int NOT NULL,
  PRIMARY KEY (`apiKeyId`),
  KEY `ApiKey_ibfk_1` (`userId`),
  KEY `ApiKey_ibfk_2` (`apiGroupId`),
  CONSTRAINT `ApiKey_ibfk_1` FOREIGN KEY (`userId`) REFERENCES `htp_User` (`userId`),
  CONSTRAINT `ApiKey_ibfk_2` FOREIGN KEY (`apiGroupId`) REFERENCES `ApiGroup` (`apiGroupId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ApiKey`
--

LOCK TABLES `ApiKey` WRITE;
/*!40000 ALTER TABLE `ApiKey` DISABLE KEYS */;
/*!40000 ALTER TABLE `ApiKey` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `Assignment`
--

DROP TABLE IF EXISTS `Assignment`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `Assignment` (
  `assignmentId` int NOT NULL AUTO_INCREMENT,
  `taskId` int NOT NULL,
  `agentId` int NOT NULL,
  `benchmark` varchar(50) NOT NULL,
  PRIMARY KEY (`assignmentId`),
  KEY `taskId` (`taskId`),
  KEY `agentId` (`agentId`),
  CONSTRAINT `Assignment_ibfk_1` FOREIGN KEY (`taskId`) REFERENCES `Task` (`taskId`),
  CONSTRAINT `Assignment_ibfk_2` FOREIGN KEY (`agentId`) REFERENCES `Agent` (`agentId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Assignment`
--

LOCK TABLES `Assignment` WRITE;
/*!40000 ALTER TABLE `Assignment` DISABLE KEYS */;
/*!40000 ALTER TABLE `Assignment` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `Chunk`
--

DROP TABLE IF EXISTS `Chunk`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `Chunk` (
  `chunkId` int NOT NULL AUTO_INCREMENT,
  `taskId` int NOT NULL,
  `skip` bigint unsigned NOT NULL,
  `length` bigint unsigned NOT NULL,
  `agentId` int DEFAULT NULL,
  `dispatchTime` bigint NOT NULL,
  `solveTime` bigint NOT NULL,
  `checkpoint` bigint unsigned NOT NULL,
  `progress` int DEFAULT NULL,
  `state` int NOT NULL,
  `cracked` int NOT NULL,
  `speed` bigint NOT NULL,
  PRIMARY KEY (`chunkId`),
  KEY `taskId` (`taskId`),
  KEY `progress` (`progress`),
  KEY `agentId` (`agentId`),
  KEY `idx_task_progress_length` (`taskId`,`progress`,`length`),
  CONSTRAINT `Chunk_ibfk_1` FOREIGN KEY (`taskId`) REFERENCES `Task` (`taskId`),
  CONSTRAINT `Chunk_ibfk_2` FOREIGN KEY (`agentId`) REFERENCES `Agent` (`agentId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Chunk`
--

LOCK TABLES `Chunk` WRITE;
/*!40000 ALTER TABLE `Chunk` DISABLE KEYS */;
/*!40000 ALTER TABLE `Chunk` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `Config`
--

DROP TABLE IF EXISTS `Config`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `Config` (
  `configId` int NOT NULL AUTO_INCREMENT,
  `configSectionId` int NOT NULL,
  `item` varchar(80) NOT NULL,
  `value` text NOT NULL,
  PRIMARY KEY (`configId`),
  KEY `configSectionId` (`configSectionId`),
  CONSTRAINT `Config_ibfk_1` FOREIGN KEY (`configSectionId`) REFERENCES `ConfigSection` (`configSectionId`)
) ENGINE=InnoDB AUTO_INCREMENT=80 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Config`
--

LOCK TABLES `Config` WRITE;
/*!40000 ALTER TABLE `Config` DISABLE KEYS */;
INSERT INTO `Config` VALUES (1,1,'agenttimeout','30'),(2,1,'benchtime','30'),(3,1,'chunktime','600'),(4,1,'chunktimeout','30'),(9,1,'fieldseparator',':'),(10,1,'hashlistAlias','#HL#'),(11,1,'statustimer','5'),(12,4,'timefmt','d.m.Y, H:i:s'),(13,1,'blacklistChars','&|`\"\'{}()[]$<>;'),(14,3,'numLogEntries','5000'),(15,1,'disptolerance','20'),(16,3,'batchSize','50000'),(18,2,'yubikey_id',''),(19,2,'yubikey_key',''),(20,2,'yubikey_url','https://api.yubico.com/wsapi/2.0/verify'),(22,3,'pagingSize','5000'),(23,3,'plainTextMaxLength','200'),(24,3,'hashMaxLength','1024'),(25,5,'emailSender','hashtopolis@example.org'),(26,5,'emailSenderName','Hashtopolis'),(27,5,'baseHost',''),(28,3,'maxHashlistSize','5000000'),(29,4,'hideImportMasks','1'),(30,7,'telegramBotToken',''),(31,5,'contactEmail',''),(32,5,'voucherDeletion','0'),(33,4,'hashesPerPage','1000'),(34,4,'hideIpInfo','0'),(35,1,'defaultBenchmark','1'),(36,4,'showTaskPerformance','0'),(41,4,'agentStatLimit','100'),(42,1,'agentDataLifetime','3600'),(43,4,'agentStatTension','0'),(44,6,'multicastEnable','0'),(45,6,'multicastDevice','eth0'),(46,6,'multicastTransferRateEnable','0'),(47,6,'multicastTranserRate','500000'),(48,1,'disableTrimming','0'),(49,5,'serverLogLevel','20'),(50,7,'notificationsProxyEnable','0'),(60,7,'notificationsProxyServer',''),(61,7,'notificationsProxyPort','8080'),(62,7,'notificationsProxyType','HTTP'),(63,1,'priority0Start','0'),(64,5,'baseUrl',''),(65,4,'maxSessionLength','48'),(66,1,'hashcatBrainEnable','0'),(67,1,'hashcatBrainHost',''),(68,1,'hashcatBrainPort','0'),(69,1,'hashcatBrainPass',''),(70,1,'hashlistImportCheck','0'),(71,5,'allowDeregister','0'),(72,4,'agentTempThreshold1','70'),(73,4,'agentTempThreshold2','80'),(74,4,'agentUtilThreshold1','90'),(75,4,'agentUtilThreshold2','75'),(76,3,'uApiSendTaskIsComplete','0'),(77,1,'hcErrorIgnore','DeviceGetFanSpeed'),(78,3,'defaultPageSize','10000'),(79,3,'maxPageSize','50000');
/*!40000 ALTER TABLE `Config` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ConfigSection`
--

DROP TABLE IF EXISTS `ConfigSection`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ConfigSection` (
  `configSectionId` int NOT NULL AUTO_INCREMENT,
  `sectionName` varchar(100) NOT NULL,
  PRIMARY KEY (`configSectionId`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ConfigSection`
--

LOCK TABLES `ConfigSection` WRITE;
/*!40000 ALTER TABLE `ConfigSection` DISABLE KEYS */;
INSERT INTO `ConfigSection` VALUES (1,'Cracking/Tasks'),(2,'Yubikey'),(3,'Finetuning'),(4,'UI'),(5,'Server'),(6,'Multicast'),(7,'Notifications');
/*!40000 ALTER TABLE `ConfigSection` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `CrackerBinary`
--

DROP TABLE IF EXISTS `CrackerBinary`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `CrackerBinary` (
  `crackerBinaryId` int NOT NULL AUTO_INCREMENT,
  `crackerBinaryTypeId` int NOT NULL,
  `version` varchar(20) NOT NULL,
  `downloadUrl` varchar(150) NOT NULL,
  `binaryName` varchar(50) NOT NULL,
  PRIMARY KEY (`crackerBinaryId`),
  KEY `crackerBinaryTypeId` (`crackerBinaryTypeId`),
  CONSTRAINT `CrackerBinary_ibfk_1` FOREIGN KEY (`crackerBinaryTypeId`) REFERENCES `CrackerBinaryType` (`crackerBinaryTypeId`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `CrackerBinary`
--

LOCK TABLES `CrackerBinary` WRITE;
/*!40000 ALTER TABLE `CrackerBinary` DISABLE KEYS */;
INSERT INTO `CrackerBinary` VALUES (1,1,'7.1.2','https://hashcat.net/files/hashcat-7.1.2.7z','hashcat');
/*!40000 ALTER TABLE `CrackerBinary` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `CrackerBinaryType`
--

DROP TABLE IF EXISTS `CrackerBinaryType`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `CrackerBinaryType` (
  `crackerBinaryTypeId` int NOT NULL AUTO_INCREMENT,
  `typeName` varchar(30) NOT NULL,
  `isChunkingAvailable` tinyint NOT NULL,
  PRIMARY KEY (`crackerBinaryTypeId`),
  UNIQUE KEY `typeName` (`typeName`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `CrackerBinaryType`
--

LOCK TABLES `CrackerBinaryType` WRITE;
/*!40000 ALTER TABLE `CrackerBinaryType` DISABLE KEYS */;
INSERT INTO `CrackerBinaryType` VALUES (1,'hashcat',1);
/*!40000 ALTER TABLE `CrackerBinaryType` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `File`
--

DROP TABLE IF EXISTS `File`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `File` (
  `fileId` int NOT NULL AUTO_INCREMENT,
  `filename` varchar(100) NOT NULL,
  `size` bigint NOT NULL,
  `isSecret` tinyint NOT NULL,
  `fileType` int NOT NULL,
  `accessGroupId` int NOT NULL,
  `lineCount` bigint DEFAULT NULL,
  PRIMARY KEY (`fileId`),
  KEY `File_ibfk_1` (`accessGroupId`),
  CONSTRAINT `File_ibfk_1` FOREIGN KEY (`accessGroupId`) REFERENCES `AccessGroup` (`accessGroupId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `File`
--

LOCK TABLES `File` WRITE;
/*!40000 ALTER TABLE `File` DISABLE KEYS */;
/*!40000 ALTER TABLE `File` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `FileDelete`
--

DROP TABLE IF EXISTS `FileDelete`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `FileDelete` (
  `fileDeleteId` int NOT NULL AUTO_INCREMENT,
  `filename` varchar(256) NOT NULL,
  `time` bigint NOT NULL,
  PRIMARY KEY (`fileDeleteId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `FileDelete`
--

LOCK TABLES `FileDelete` WRITE;
/*!40000 ALTER TABLE `FileDelete` DISABLE KEYS */;
/*!40000 ALTER TABLE `FileDelete` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `FileDownload`
--

DROP TABLE IF EXISTS `FileDownload`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `FileDownload` (
  `fileDownloadId` int NOT NULL AUTO_INCREMENT,
  `time` bigint NOT NULL,
  `fileId` int NOT NULL,
  `status` int NOT NULL,
  PRIMARY KEY (`fileDownloadId`),
  KEY `FileDownload_ibkf_1` (`fileId`),
  CONSTRAINT `FileDownload_ibkf_1` FOREIGN KEY (`fileId`) REFERENCES `File` (`fileId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `FileDownload`
--

LOCK TABLES `FileDownload` WRITE;
/*!40000 ALTER TABLE `FileDownload` DISABLE KEYS */;
/*!40000 ALTER TABLE `FileDownload` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `FilePretask`
--

DROP TABLE IF EXISTS `FilePretask`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `FilePretask` (
  `filePretaskId` int NOT NULL AUTO_INCREMENT,
  `fileId` int NOT NULL,
  `pretaskId` int NOT NULL,
  PRIMARY KEY (`filePretaskId`),
  KEY `fileId` (`fileId`),
  KEY `pretaskId` (`pretaskId`),
  CONSTRAINT `FilePretask_ibfk_1` FOREIGN KEY (`fileId`) REFERENCES `File` (`fileId`),
  CONSTRAINT `FilePretask_ibfk_2` FOREIGN KEY (`pretaskId`) REFERENCES `Pretask` (`pretaskId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `FilePretask`
--

LOCK TABLES `FilePretask` WRITE;
/*!40000 ALTER TABLE `FilePretask` DISABLE KEYS */;
/*!40000 ALTER TABLE `FilePretask` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `FileTask`
--

DROP TABLE IF EXISTS `FileTask`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `FileTask` (
  `fileTaskId` int NOT NULL AUTO_INCREMENT,
  `fileId` int NOT NULL,
  `taskId` int NOT NULL,
  PRIMARY KEY (`fileTaskId`),
  KEY `fileId` (`fileId`),
  KEY `taskId` (`taskId`),
  CONSTRAINT `FileTask_ibfk_1` FOREIGN KEY (`fileId`) REFERENCES `File` (`fileId`),
  CONSTRAINT `FileTask_ibfk_2` FOREIGN KEY (`taskId`) REFERENCES `Task` (`taskId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `FileTask`
--

LOCK TABLES `FileTask` WRITE;
/*!40000 ALTER TABLE `FileTask` DISABLE KEYS */;
/*!40000 ALTER TABLE `FileTask` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `Hash`
--

DROP TABLE IF EXISTS `Hash`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `Hash` (
  `hashId` int NOT NULL AUTO_INCREMENT,
  `hashlistId` int NOT NULL,
  `hash` mediumtext NOT NULL,
  `salt` varchar(256) DEFAULT NULL,
  `plaintext` varchar(256) DEFAULT NULL,
  `timeCracked` bigint DEFAULT NULL,
  `chunkId` int DEFAULT NULL,
  `isCracked` tinyint NOT NULL,
  `crackPos` bigint NOT NULL,
  PRIMARY KEY (`hashId`),
  KEY `hashlistId` (`hashlistId`),
  KEY `chunkId` (`chunkId`),
  KEY `isCracked` (`isCracked`),
  KEY `hash` (`hash`(500)),
  KEY `timeCracked` (`timeCracked`),
  CONSTRAINT `Hash_ibfk_1` FOREIGN KEY (`hashlistId`) REFERENCES `Hashlist` (`hashlistId`),
  CONSTRAINT `Hash_ibfk_2` FOREIGN KEY (`chunkId`) REFERENCES `Chunk` (`chunkId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Hash`
--

LOCK TABLES `Hash` WRITE;
/*!40000 ALTER TABLE `Hash` DISABLE KEYS */;
/*!40000 ALTER TABLE `Hash` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `HashBinary`
--

DROP TABLE IF EXISTS `HashBinary`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `HashBinary` (
  `hashBinaryId` int NOT NULL AUTO_INCREMENT,
  `hashlistId` int NOT NULL,
  `essid` varchar(100) NOT NULL,
  `hash` longtext NOT NULL,
  `plaintext` varchar(1024) DEFAULT NULL,
  `timeCracked` bigint DEFAULT NULL,
  `chunkId` int DEFAULT NULL,
  `isCracked` tinyint NOT NULL,
  `crackPos` bigint NOT NULL,
  PRIMARY KEY (`hashBinaryId`),
  KEY `hashlistId` (`hashlistId`),
  KEY `chunkId` (`chunkId`),
  CONSTRAINT `HashBinary_ibfk_1` FOREIGN KEY (`hashlistId`) REFERENCES `Hashlist` (`hashlistId`),
  CONSTRAINT `HashBinary_ibfk_2` FOREIGN KEY (`chunkId`) REFERENCES `Chunk` (`chunkId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `HashBinary`
--

LOCK TABLES `HashBinary` WRITE;
/*!40000 ALTER TABLE `HashBinary` DISABLE KEYS */;
/*!40000 ALTER TABLE `HashBinary` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `HashType`
--

DROP TABLE IF EXISTS `HashType`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `HashType` (
  `hashTypeId` int NOT NULL AUTO_INCREMENT,
  `description` varchar(256) NOT NULL,
  `isSalted` tinyint NOT NULL,
  `isSlowHash` tinyint NOT NULL,
  PRIMARY KEY (`hashTypeId`)
) ENGINE=InnoDB AUTO_INCREMENT=100000 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `HashType`
--

LOCK TABLES `HashType` WRITE;
/*!40000 ALTER TABLE `HashType` DISABLE KEYS */;
INSERT INTO `HashType` VALUES (0,'MD5',0,0),(10,'md5($pass.$salt)',1,0),(11,'Joomla < 2.5.18',1,0),(12,'PostgreSQL',1,0),(20,'md5($salt.$pass)',1,0),(21,'osCommerce, xt:Commerce',1,0),(22,'Juniper Netscreen/SSG (ScreenOS)',1,0),(23,'Skype',1,0),(24,'SolarWinds Serv-U',0,0),(30,'md5(utf16le($pass).$salt)',1,0),(40,'md5($salt.utf16le($pass))',1,0),(50,'HMAC-MD5 (key = $pass)',1,0),(60,'HMAC-MD5 (key = $salt)',1,0),(70,'md5(utf16le($pass))',0,0),(100,'SHA1',0,0),(101,'nsldap, SHA-1(Base64), Netscape LDAP SHA',0,0),(110,'sha1($pass.$salt)',1,0),(111,'nsldaps, SSHA-1(Base64), Netscape LDAP SSHA',0,0),(112,'Oracle S: Type (Oracle 11+)',1,0),(120,'sha1($salt.$pass)',1,0),(121,'SMF >= v1.1',1,0),(122,'OS X v10.4, v10.5, v10.6',0,0),(124,'Django (SHA-1)',0,0),(125,'ArubaOS',0,0),(130,'sha1(utf16le($pass).$salt)',1,0),(131,'MSSQL(2000)',0,0),(132,'MSSQL(2005)',0,0),(133,'PeopleSoft',0,0),(140,'sha1($salt.utf16le($pass))',1,0),(141,'EPiServer 6.x < v4',0,0),(150,'HMAC-SHA1 (key = $pass)',1,0),(160,'HMAC-SHA1 (key = $salt)',1,0),(170,'sha1(utf16le($pass))',0,0),(200,'MySQL323',0,0),(300,'MySQL4.1/MySQL5+',0,0),(400,'phpass, MD5(Wordpress), MD5(Joomla), MD5(phpBB3)',0,0),(500,'md5crypt, MD5(Unix), FreeBSD MD5, Cisco-IOS MD5 2',0,0),(501,'Juniper IVE',0,0),(600,'BLAKE2b-512',0,0),(610,'BLAKE2b-512($pass.$salt)',1,0),(620,'BLAKE2b-512($salt.$pass)',1,0),(900,'MD4',0,0),(1000,'NTLM',0,0),(1100,'Domain Cached Credentials (DCC), MS Cache',1,0),(1300,'SHA-224',0,0),(1310,'sha224($pass.$salt)',1,0),(1320,'sha224($salt.$pass)',1,0),(1400,'SHA256',0,0),(1410,'sha256($pass.$salt)',1,0),(1411,'SSHA-256(Base64), LDAP {SSHA256}',0,0),(1420,'sha256($salt.$pass)',1,0),(1421,'hMailServer',0,0),(1430,'sha256(utf16le($pass).$salt)',1,0),(1440,'sha256($salt.utf16le($pass))',1,0),(1441,'EPiServer 6.x >= v4',0,0),(1450,'HMAC-SHA256 (key = $pass)',1,0),(1460,'HMAC-SHA256 (key = $salt)',1,0),(1470,'sha256(utf16le($pass))',0,0),(1500,'descrypt, DES(Unix), Traditional DES',0,0),(1600,'md5apr1, MD5(APR), Apache MD5',0,0),(1700,'SHA512',0,0),(1710,'sha512($pass.$salt)',1,0),(1711,'SSHA-512(Base64), LDAP {SSHA512}',0,0),(1720,'sha512($salt.$pass)',1,0),(1722,'OS X v10.7',0,0),(1730,'sha512(utf16le($pass).$salt)',1,0),(1731,'MSSQL(2012), MSSQL(2014)',0,0),(1740,'sha512($salt.utf16le($pass))',1,0),(1750,'HMAC-SHA512 (key = $pass)',1,0),(1760,'HMAC-SHA512 (key = $salt)',1,0),(1770,'sha512(utf16le($pass))',0,0),(1800,'sha512crypt, SHA512(Unix)',0,0),(2000,'STDOUT',0,0),(2100,'Domain Cached Credentials 2 (DCC2), MS Cache',0,1),(2400,'Cisco-PIX MD5',0,0),(2410,'Cisco-ASA MD5',1,0),(2500,'WPA/WPA2',0,1),(2501,'WPA-EAPOL-PMK',0,1),(2600,'md5(md5($pass))',0,0),(2611,'vBulletin < v3.8.5',1,0),(2612,'PHPS',0,0),(2630,'md5(md5($pass.$salt))',1,0),(2711,'vBulletin >= v3.8.5',1,0),(2811,'IPB2+, MyBB1.2+',1,0),(3000,'LM',0,0),(3100,'Oracle H: Type (Oracle 7+), DES(Oracle)',1,0),(3200,'bcrypt, Blowfish(OpenBSD)',0,0),(3500,'md5(md5(md5($pass)))',0,0),(3610,'md5(md5(md5($pass)).$salt)',1,0),(3710,'md5($salt.md5($pass))',1,0),(3711,'Mediawiki B type',0,0),(3730,'md5($salt1.strtoupper(md5($salt2.$pass)))',0,0),(3800,'md5($salt.$pass.$salt)',1,0),(3910,'md5(md5($pass).md5($salt))',1,0),(4010,'md5($salt.md5($salt.$pass))',1,0),(4110,'md5($salt.md5($pass.$salt))',1,0),(4300,'md5(strtoupper(md5($pass)))',0,0),(4400,'md5(sha1($pass))',0,0),(4410,'md5(sha1($pass).$salt)',1,0),(4420,'md5(sha1($pass.$salt))',1,0),(4430,'md5(sha1($salt.$pass))',1,0),(4500,'sha1(sha1($pass))',0,0),(4510,'sha1(sha1($pass).$salt)',1,0),(4520,'sha1($salt.sha1($pass))',1,0),(4521,'Redmine Project Management Web App',0,0),(4522,'PunBB',0,0),(4700,'sha1(md5($pass))',0,0),(4710,'sha1(md5($pass).$salt)',1,0),(4711,'Huawei sha1(md5($pass).$salt)',1,0),(4800,'MD5(Chap), iSCSI CHAP authentication',1,0),(4900,'sha1($salt.$pass.$salt)',1,0),(5000,'SHA-3(Keccak)',0,0),(5100,'Half MD5',0,0),(5200,'Password Safe v3',0,1),(5300,'IKE-PSK MD5',0,0),(5400,'IKE-PSK SHA1',0,0),(5500,'NetNTLMv1-VANILLA / NetNTLMv1+ESS',0,0),(5600,'NetNTLMv2',0,0),(5700,'Cisco-IOS SHA256',0,0),(5720,'Cisco-ISE Hashed Password (SHA256)',0,0),(5800,'Samsung Android Password/PIN',1,0),(6000,'RipeMD160',0,0),(6050,'HMAC-RIPEMD160 (key = $pass)',1,0),(6060,'HMAC-RIPEMD160 (key = $salt)',1,0),(6100,'Whirlpool',0,0),(6211,'TrueCrypt 5.0+ PBKDF2-HMAC-RipeMD160 + AES/Serpent/Twofish',0,1),(6212,'TrueCrypt 5.0+ PBKDF2-HMAC-RipeMD160 + AES-Twofish/Serpent-AES/Twofish-Serpent',0,1),(6213,'TrueCrypt 5.0+ PBKDF2-HMAC-RipeMD160 + AES-Twofish-Serpent/Serpent-Twofish-AES',0,1),(6221,'TrueCrypt 5.0+ SHA512 + AES/Serpent/Twofish',0,1),(6222,'TrueCrypt 5.0+ SHA512 + AES-Twofish/Serpent-AES/Twofish-Serpent',0,1),(6223,'TrueCrypt 5.0+ SHA512 + AES-Twofish-Serpent/Serpent-Twofish-AES',0,1),(6231,'TrueCrypt 5.0+ Whirlpool + AES/Serpent/Twofish',0,1),(6232,'TrueCrypt 5.0+ Whirlpool + AES-Twofish/Serpent-AES/Twofish-Serpent',0,1),(6233,'TrueCrypt 5.0+ Whirlpool + AES-Twofish-Serpent/Serpent-Twofish-AES',0,1),(6241,'TrueCrypt 5.0+ PBKDF2-HMAC-RipeMD160 + AES/Serpent/Twofish + boot',0,1),(6242,'TrueCrypt 5.0+ PBKDF2-HMAC-RipeMD160 + AES-Twofish/Serpent-AES/Twofish-Serpent + boot',0,1),(6243,'TrueCrypt 5.0+ PBKDF2-HMAC-RipeMD160 + AES-Twofish-Serpent/Serpent-Twofish-AES + boot',0,1),(6300,'AIX {smd5}',0,0),(6400,'AIX {ssha256}',0,1),(6500,'AIX {ssha512}',0,1),(6600,'1Password, Agile Keychain',0,1),(6700,'AIX {ssha1}',0,1),(6800,'Lastpass',1,1),(6900,'GOST R 34.11-94',0,0),(7000,'Fortigate (FortiOS)',0,0),(7100,'OS X v10.8 / v10.9',0,1),(7200,'GRUB 2',0,1),(7300,'IPMI2 RAKP HMAC-SHA1',1,0),(7350,'IPMI2 RAKP HMAC-MD5',0,0),(7400,'sha256crypt, SHA256(Unix)',0,0),(7401,'MySQL $A$ (sha256crypt)',0,0),(7500,'Kerberos 5 AS-REQ Pre-Auth',0,0),(7700,'SAP CODVN B (BCODE)',0,0),(7701,'SAP CODVN B (BCODE) from RFC_READ_TABLE',0,0),(7800,'SAP CODVN F/G (PASSCODE)',0,0),(7801,'SAP CODVN F/G (PASSCODE) from RFC_READ_TABLE',0,0),(7900,'Drupal7',0,0),(8000,'Sybase ASE',0,0),(8100,'Citrix Netscaler',0,0),(8200,'1Password, Cloud Keychain',0,1),(8300,'DNSSEC (NSEC3)',1,0),(8400,'WBB3, Woltlab Burning Board 3',1,0),(8500,'RACF',0,0),(8501,'AS/400 DES',0,0),(8600,'Lotus Notes/Domino 5',0,0),(8700,'Lotus Notes/Domino 6',0,0),(8800,'Android FDE <= 4.3',0,1),(8900,'scrypt',1,0),(9000,'Password Safe v2',0,0),(9100,'Lotus Notes/Domino',0,1),(9200,'Cisco $8$',0,1),(9300,'Cisco $9$',0,0),(9400,'Office 2007',0,1),(9500,'Office 2010',0,1),(9600,'Office 2013',0,1),(9700,'MS Office ⇐ 2003 MD5 + RC4, oldoffice$0, oldoffice$1',0,0),(9710,'MS Office <= 2003 $0/$1, MD5 + RC4, collider #1',0,0),(9720,'MS Office <= 2003 $0/$1, MD5 + RC4, collider #2',0,0),(9800,'MS Office ⇐ 2003 SHA1 + RC4, oldoffice$3, oldoffice$4',0,0),(9810,'MS Office <= 2003 $3, SHA1 + RC4, collider #1',0,0),(9820,'MS Office <= 2003 $3, SHA1 + RC4, collider #2',0,0),(9900,'Radmin2',0,0),(10000,'Django (PBKDF2-SHA256)',0,1),(10100,'SipHash',1,0),(10200,'Cram MD5',0,0),(10300,'SAP CODVN H (PWDSALTEDHASH) iSSHA-1',0,0),(10400,'PDF 1.1 - 1.3 (Acrobat 2 - 4)',0,0),(10410,'PDF 1.1 - 1.3 (Acrobat 2 - 4), collider #1',0,0),(10420,'PDF 1.1 - 1.3 (Acrobat 2 - 4), collider #2',0,0),(10500,'PDF 1.4 - 1.6 (Acrobat 5 - 8)',0,0),(10510,'PDF 1.3 - 1.6 (Acrobat 4 - 8) w/ RC4-40',0,1),(10600,'PDF 1.7 Level 3 (Acrobat 9)',0,0),(10700,'PDF 1.7 Level 8 (Acrobat 10 - 11)',0,0),(10800,'SHA384',0,0),(10810,'sha384($pass.$salt)',1,0),(10820,'sha384($salt.$pass)',1,0),(10830,'sha384(utf16le($pass).$salt)',1,0),(10840,'sha384($salt.utf16le($pass))',1,0),(10870,'sha384(utf16le($pass))',0,0),(10900,'PBKDF2-HMAC-SHA256',0,1),(10901,'RedHat 389-DS LDAP (PBKDF2-HMAC-SHA256)',0,1),(11000,'PrestaShop',1,0),(11100,'PostgreSQL Challenge-Response Authentication (MD5)',0,0),(11200,'MySQL Challenge-Response Authentication (SHA1)',0,0),(11300,'Bitcoin/Litecoin wallet.dat',0,1),(11400,'SIP digest authentication (MD5)',0,0),(11500,'CRC32',1,0),(11600,'7-Zip',0,0),(11700,'GOST R 34.11-2012 (Streebog) 256-bit',0,0),(11750,'HMAC-Streebog-256 (key = $pass), big-endian',0,0),(11760,'HMAC-Streebog-256 (key = $salt), big-endian',0,0),(11800,'GOST R 34.11-2012 (Streebog) 512-bit',0,0),(11850,'HMAC-Streebog-512 (key = $pass), big-endian',0,0),(11860,'HMAC-Streebog-512 (key = $salt), big-endian',0,0),(11900,'PBKDF2-HMAC-MD5',0,1),(12000,'PBKDF2-HMAC-SHA1',0,1),(12001,'Atlassian (PBKDF2-HMAC-SHA1)',0,1),(12100,'PBKDF2-HMAC-SHA512',0,1),(12150,'Apache Shiro 1 SHA-512',0,1),(12200,'eCryptfs',0,1),(12300,'Oracle T: Type (Oracle 12+)',0,1),(12400,'BSDiCrypt, Extended DES',0,0),(12500,'RAR3-hp',0,0),(12600,'ColdFusion 10+',1,0),(12700,'Blockchain, My Wallet',0,1),(12800,'MS-AzureSync PBKDF2-HMAC-SHA256',0,1),(12900,'Android FDE (Samsung DEK)',0,1),(13000,'RAR5',0,1),(13100,'Kerberos 5 TGS-REP etype 23',0,0),(13200,'AxCrypt',0,0),(13300,'AxCrypt in memory SHA1',0,0),(13400,'Keepass 1/2 AES/Twofish with/without keyfile',0,0),(13500,'PeopleSoft PS_TOKEN',1,0),(13600,'WinZip',0,1),(13711,'VeraCrypt PBKDF2-HMAC-RIPEMD160 + AES, Serpent, Twofish',0,1),(13712,'VeraCrypt PBKDF2-HMAC-RIPEMD160 + AES-Twofish, Serpent-AES, Twofish-Serpent',0,1),(13713,'VeraCrypt PBKDF2-HMAC-RIPEMD160 + Serpent-Twofish-AES',0,1),(13721,'VeraCrypt PBKDF2-HMAC-SHA512 + AES, Serpent, Twofish',0,1),(13722,'VeraCrypt PBKDF2-HMAC-SHA512 + AES-Twofish, Serpent-AES, Twofish-Serpent',0,1),(13723,'VeraCrypt PBKDF2-HMAC-SHA512 + Serpent-Twofish-AES',0,1),(13731,'VeraCrypt PBKDF2-HMAC-Whirlpool + AES, Serpent, Twofish',0,1),(13732,'VeraCrypt PBKDF2-HMAC-Whirlpool + AES-Twofish, Serpent-AES, Twofish-Serpent',0,1),(13733,'VeraCrypt PBKDF2-HMAC-Whirlpool + Serpent-Twofish-AES',0,1),(13741,'VeraCrypt PBKDF2-HMAC-RIPEMD160 + boot-mode + AES',0,1),(13742,'VeraCrypt PBKDF2-HMAC-RIPEMD160 + boot-mode + AES-Twofish',0,1),(13743,'VeraCrypt PBKDF2-HMAC-RIPEMD160 + boot-mode + AES-Twofish-Serpent',0,1),(13751,'VeraCrypt PBKDF2-HMAC-SHA256 + AES, Serpent, Twofish',0,1),(13752,'VeraCrypt PBKDF2-HMAC-SHA256 + AES-Twofish, Serpent-AES, Twofish-Serpent',0,1),(13753,'VeraCrypt PBKDF2-HMAC-SHA256 + Serpent-Twofish-AES',0,1),(13761,'VeraCrypt PBKDF2-HMAC-SHA256 + boot-mode (PIM + AES | Twofish)',0,1),(13762,'VeraCrypt PBKDF2-HMAC-SHA256 + boot-mode + Serpent-AES',0,1),(13763,'VeraCrypt PBKDF2-HMAC-SHA256 + boot-mode + Serpent-Twofish-AES',0,1),(13771,'VeraCrypt Streebog-512 + XTS 512 bit',0,1),(13772,'VeraCrypt Streebog-512 + XTS 1024 bit',0,1),(13773,'VeraCrypt Streebog-512 + XTS 1536 bit',0,1),(13781,'VeraCrypt Streebog-512 + XTS 512 bit + boot-mode (legacy)',0,1),(13782,'VeraCrypt Streebog-512 + XTS 1024 bit + boot-mode (legacy)',0,1),(13783,'VeraCrypt Streebog-512 + XTS 1536 bit + boot-mode (legacy)',0,1),(13800,'Windows 8+ phone PIN/Password',1,0),(13900,'OpenCart',1,0),(14000,'DES (PT = $salt, key = $pass)',1,0),(14100,'3DES (PT = $salt, key = $pass)',1,0),(14200,'RACF KDFAES',0,1),(14400,'sha1(CX)',1,0),(14500,'Linux Kernel Crypto API (2.4)',0,0),(14600,'LUKS 10',0,1),(14700,'iTunes Backup < 10.0 11',0,1),(14800,'iTunes Backup >= 10.0 11',0,1),(14900,'Skip32 12',1,0),(15000,'FileZilla Server >= 0.9.55',1,0),(15100,'Juniper/NetBSD sha1crypt',0,1),(15200,'Blockchain, My Wallet, V2',0,0),(15300,'DPAPI masterkey file v1 and v2',0,1),(15310,'DPAPI masterkey file v1 (context 3)',0,1),(15400,'ChaCha20',0,0),(15500,'JKS Java Key Store Private Keys (SHA1)',0,0),(15600,'Ethereum Wallet, PBKDF2-HMAC-SHA256',0,1),(15700,'Ethereum Wallet, SCRYPT',0,0),(15900,'DPAPI master key file version 2 + Active Directory domain context',0,1),(15910,'DPAPI masterkey file v2 (context 3)',0,1),(16000,'Tripcode',0,0),(16100,'TACACS+',0,0),(16200,'Apple Secure Notes',0,1),(16300,'Ethereum Pre-Sale Wallet, PBKDF2-HMAC-SHA256',0,1),(16400,'CRAM-MD5 Dovecot',0,0),(16500,'JWT (JSON Web Token)',0,0),(16501,'Perl Mojolicious session cookie (HMAC-SHA256, >= v9.19)',0,0),(16600,'Electrum Wallet (Salt-Type 1-3)',0,0),(16700,'FileVault 2',0,1),(16800,'WPA-PMKID-PBKDF2',0,1),(16801,'WPA-PMKID-PMK',0,1),(16900,'Ansible Vault',0,1),(17010,'GPG (AES-128/AES-256 (SHA-1($pass)))',0,1),(17020,'GPG (AES-128/AES-256 (SHA-512($pass)))',0,1),(17030,'GPG (AES-128/AES-256 (SHA-256($pass)))',0,1),(17040,'GPG (CAST5 (SHA-1($pass)))',0,1),(17200,'PKZIP (Compressed)',0,0),(17210,'PKZIP (Uncompressed)',0,0),(17220,'PKZIP (Compressed Multi-File)',0,0),(17225,'PKZIP (Mixed Multi-File)',0,0),(17230,'PKZIP (Compressed Multi-File Checksum-Only)',0,0),(17300,'SHA3-224',0,0),(17400,'SHA3-256',0,0),(17500,'SHA3-384',0,0),(17600,'SHA3-512',0,0),(17700,'Keccak-224',0,0),(17800,'Keccak-256',0,0),(17900,'Keccak-384',0,0),(18000,'Keccak-512',0,0),(18100,'TOTP (HMAC-SHA1)',1,0),(18200,'Kerberos 5 AS-REP etype 23',0,1),(18300,'Apple File System (APFS)',0,1),(18400,'Open Document Format (ODF) 1.2 (SHA-256, AES)',0,1),(18500,'sha1(md5(md5($pass)))',0,0),(18600,'Open Document Format (ODF) 1.1 (SHA-1, Blowfish)',0,1),(18700,'Java Object hashCode()',0,1),(18800,'Blockchain, My Wallet, Second Password (SHA256)',0,1),(18900,'Android Backup',0,1),(19000,'QNX /etc/shadow (MD5)',0,1),(19100,'QNX /etc/shadow (SHA256)',0,1),(19200,'QNX /etc/shadow (SHA512)',0,1),(19210,'QNX 7 /etc/shadow (SHA512)',0,1),(19300,'sha1($salt1.$pass.$salt2)',0,0),(19500,'Ruby on Rails Restful-Authentication',0,0),(19600,'Kerberos 5 TGS-REP etype 17 (AES128-CTS-HMAC-SHA1-96)',0,1),(19700,'Kerberos 5 TGS-REP etype 18 (AES256-CTS-HMAC-SHA1-96)',0,1),(19800,'Kerberos 5, etype 17, Pre-Auth',0,1),(19900,'Kerberos 5, etype 18, Pre-Auth',0,1),(20011,'DiskCryptor SHA512 + XTS 512 bit (AES) / DiskCryptor SHA512 + XTS 512 bit (Twofish) / DiskCryptor SHA512 + XTS 512 bit (Serpent)',0,1),(20012,'DiskCryptor SHA512 + XTS 1024 bit (AES-Twofish) / DiskCryptor SHA512 + XTS 1024 bit (Twofish-Serpent) / DiskCryptor SHA512 + XTS 1024 bit (Serpent-AES)',0,1),(20013,'DiskCryptor SHA512 + XTS 1536 bit (AES-Twofish-Serpent)',0,1),(20200,'Python passlib pbkdf2-sha512',0,1),(20300,'Python passlib pbkdf2-sha256',0,1),(20400,'Python passlib pbkdf2-sha1',0,0),(20500,'PKZIP Master Key',0,0),(20510,'PKZIP Master Key (6 byte optimization)',0,0),(20600,'Oracle Transportation Management (SHA256)',0,0),(20710,'sha256(sha256($pass).$salt)',1,0),(20711,'AuthMe sha256',0,0),(20712,'RSA Security Analytics / NetWitness (sha256)',1,0),(20720,'sha256($salt.sha256($pass))',1,0),(20730,'sha256(sha256($pass.$salt))',1,0),(20800,'sha256(md5($pass))',0,0),(20900,'md5(sha1($pass).md5($pass).sha1($pass))',0,0),(21000,'BitShares v0.x - sha512(sha512_bin(pass))',0,0),(21100,'sha1(md5($pass.$salt))',1,0),(21200,'md5(sha1($salt).md5($pass))',1,0),(21300,'md5($salt.sha1($salt.$pass))',1,0),(21310,'md5($salt1.sha1($salt2.$pass))',1,0),(21400,'sha256(sha256_bin(pass))',0,0),(21420,'sha256($salt.sha256_bin($pass))',1,0),(21500,'SolarWinds Orion',0,0),(21501,'SolarWinds Orion v2',0,0),(21600,'Web2py pbkdf2-sha512',0,0),(21700,'Electrum Wallet (Salt-Type 4)',0,0),(21800,'Electrum Wallet (Salt-Type 5)',0,0),(21900,'md5(md5(md5($pass.$salt1)).$salt2)',0,0),(22000,'WPA-PBKDF2-PMKID+EAPOL',0,0),(22001,'WPA-PMK-PMKID+EAPOL',0,0),(22100,'BitLocker',0,0),(22200,'Citrix NetScaler (SHA512)',0,0),(22300,'sha256($salt.$pass.$salt)',1,0),(22301,'Telegram client app passcode (SHA256)',0,0),(22400,'AES Crypt (SHA256)',0,0),(22500,'MultiBit Classic .key (MD5)',0,0),(22600,'Telegram Desktop App Passcode (PBKDF2-HMAC-SHA1)',0,0),(22700,'MultiBit HD (scrypt)',0,1),(22800,'Simpla CMS - md5($salt.$pass.md5($pass))',1,0),(22911,'RSA/DSA/EC/OPENSSH Private Keys ($0$)',0,0),(22921,'RSA/DSA/EC/OPENSSH Private Keys ($6$)',0,0),(22931,'RSA/DSA/EC/OPENSSH Private Keys ($1, $3$)',0,0),(22941,'RSA/DSA/EC/OPENSSH Private Keys ($4$)',0,0),(22951,'RSA/DSA/EC/OPENSSH Private Keys ($5$)',0,0),(23001,'SecureZIP AES-128',0,0),(23002,'SecureZIP AES-192',0,0),(23003,'SecureZIP AES-256',0,0),(23100,'Apple Keychain',0,1),(23200,'XMPP SCRAM PBKDF2-SHA1',0,0),(23300,'Apple iWork',0,0),(23400,'Bitwarden',0,0),(23500,'AxCrypt 2 AES-128',0,0),(23600,'AxCrypt 2 AES-256',0,0),(23700,'RAR3-p (Uncompressed)',0,0),(23800,'RAR3-p (Compressed)',0,0),(23900,'BestCrypt v3 Volume Encryption',0,0),(24000,'BestCrypt v4 Volume Encryption',0,1),(24100,'MongoDB ServerKey SCRAM-SHA-1',0,0),(24200,'MongoDB ServerKey SCRAM-SHA-256',0,0),(24300,'sha1($salt.sha1($pass.$salt))',1,0),(24410,'PKCS#8 Private Keys (PBKDF2-HMAC-SHA1 + 3DES/AES)',0,0),(24420,'PKCS#8 Private Keys (PBKDF2-HMAC-SHA256 + 3DES/AES)',0,0),(24500,'Telegram Desktop >= v2.1.14 (PBKDF2-HMAC-SHA512)',0,0),(24600,'SQLCipher',0,0),(24700,'Stuffit5',0,0),(24800,'Umbraco HMAC-SHA1',0,0),(24900,'Dahua Authentication MD5',0,0),(25000,'SNMPv3 HMAC-MD5-96/HMAC-SHA1-96',0,1),(25100,'SNMPv3 HMAC-MD5-96',0,1),(25200,'SNMPv3 HMAC-SHA1-96',0,1),(25300,'MS Office 2016 - SheetProtection',0,0),(25400,'PDF 1.4 - 1.6 (Acrobat 5 - 8) - edit password',0,0),(25500,'Stargazer Stellar Wallet XLM',0,0),(25600,'bcrypt(md5($pass)) / bcryptmd5',0,1),(25700,'MurmurHash',1,0),(25800,'bcrypt(sha1($pass)) / bcryptsha1',0,1),(25900,'KNX IP Secure - Device Authentication Code',0,0),(26000,'Mozilla key3.db',0,0),(26100,'Mozilla key4.db',0,0),(26200,'OpenEdge Progress Encode',0,0),(26300,'FortiGate256 (FortiOS256)',0,0),(26401,'AES-128-ECB NOKDF (PT = $salt, key = $pass)',0,0),(26402,'AES-192-ECB NOKDF (PT = $salt, key = $pass)',0,0),(26403,'AES-256-ECB NOKDF (PT = $salt, key = $pass)',0,0),(26500,'iPhone passcode (UID key + System Keybag)',0,0),(26600,'MetaMask Wallet',0,1),(26610,'MetaMask Wallet (short hash, plaintext check)',0,1),(26700,'SNMPv3 HMAC-SHA224-128',0,0),(26800,'SNMPv3 HMAC-SHA256-192',0,0),(26900,'SNMPv3 HMAC-SHA384-256',0,0),(27000,'NetNTLMv1 / NetNTLMv1+ESS (NT)',0,0),(27100,'NetNTLMv2 (NT)',0,0),(27200,'Ruby on Rails Restful Auth (one round, no sitekey)',1,0),(27300,'SNMPv3 HMAC-SHA512-384',0,0),(27400,'VMware VMX (PBKDF2-HMAC-SHA1 + AES-256-CBC)',0,0),(27500,'VirtualBox (PBKDF2-HMAC-SHA256 & AES-128-XTS)',0,1),(27600,'VirtualBox (PBKDF2-HMAC-SHA256 & AES-256-XTS)',0,1),(27700,'MultiBit Classic .wallet (scrypt)',0,0),(27800,'MurmurHash3',1,0),(27900,'CRC32C',1,0),(28000,'CRC64Jones',1,0),(28100,'Windows Hello PIN/Password',0,1),(28200,'Exodus Desktop Wallet (scrypt)',0,0),(28300,'Teamspeak 3 (channel hash)',0,0),(28400,'bcrypt(sha512($pass)) / bcryptsha512',0,0),(28501,'Bitcoin WIF private key (P2PKH), compressed',0,0),(28502,'Bitcoin WIF private key (P2PKH), uncompressed',0,0),(28503,'Bitcoin WIF private key (P2WPKH, Bech32), compressed',0,0),(28504,'Bitcoin WIF private key (P2WPKH, Bech32), uncompressed',0,0),(28505,'Bitcoin WIF private key (P2SH(P2WPKH)), compressed',0,0),(28506,'Bitcoin WIF private key (P2SH(P2WPKH)), uncompressed',0,0),(28600,'PostgreSQL SCRAM-SHA-256',0,1),(28700,'Amazon AWS4-HMAC-SHA256',0,0),(28800,'Kerberos 5, etype 17, DB',0,1),(28900,'Kerberos 5, etype 18, DB',0,1),(29000,'sha1($salt.sha1(utf16le($username).\':\'.utf16le($pass)))',0,0),(29100,'Flask Session Cookie ($salt.$salt.$pass)',0,0),(29200,'Radmin3',0,0),(29311,'TrueCrypt RIPEMD160 + XTS 512 bit',0,0),(29312,'TrueCrypt RIPEMD160 + XTS 1024 bit',0,0),(29313,'TrueCrypt RIPEMD160 + XTS 1536 bit',0,0),(29321,'TrueCrypt SHA512 + XTS 512 bit',0,0),(29322,'TrueCrypt SHA512 + XTS 1024 bit',0,0),(29323,'TrueCrypt SHA512 + XTS 1536 bit',0,0),(29331,'TrueCrypt Whirlpool + XTS 512 bit',0,0),(29332,'TrueCrypt Whirlpool + XTS 1024 bit',0,0),(29333,'TrueCrypt Whirlpool + XTS 1536 bit',0,0),(29341,'TrueCrypt RIPEMD160 + XTS 512 bit + boot-mode',0,0),(29342,'TrueCrypt RIPEMD160 + XTS 1024 bit + boot-mode',0,0),(29343,'TrueCrypt RIPEMD160 + XTS 1536 bit + boot-mode',0,0),(29411,'VeraCrypt RIPEMD160 + XTS 512 bit',0,0),(29412,'VeraCrypt RIPEMD160 + XTS 1024 bit',0,0),(29413,'VeraCrypt RIPEMD160 + XTS 1536 bit',0,0),(29421,'VeraCrypt SHA512 + XTS 512 bit',0,0),(29422,'VeraCrypt SHA512 + XTS 1024 bit',0,0),(29423,'VeraCrypt SHA512 + XTS 1536 bit',0,0),(29431,'VeraCrypt Whirlpool + XTS 512 bit',0,0),(29432,'VeraCrypt Whirlpool + XTS 1024 bit',0,0),(29433,'VeraCrypt Whirlpool + XTS 1536 bit',0,0),(29441,'VeraCrypt RIPEMD160 + XTS 512 bit + boot-mode',0,0),(29442,'VeraCrypt RIPEMD160 + XTS 1024 bit + boot-mode',0,0),(29443,'VeraCrypt RIPEMD160 + XTS 1536 bit + boot-mode',0,0),(29451,'VeraCrypt SHA256 + XTS 512 bit',0,0),(29452,'VeraCrypt SHA256 + XTS 1024 bit',0,0),(29453,'VeraCrypt SHA256 + XTS 1536 bit',0,0),(29461,'VeraCrypt SHA256 + XTS 512 bit + boot-mode',0,0),(29462,'VeraCrypt SHA256 + XTS 1024 bit + boot-mode',0,0),(29463,'VeraCrypt SHA256 + XTS 1536 bit + boot-mode',0,0),(29471,'VeraCrypt Streebog-512 + XTS 512 bit',0,0),(29472,'VeraCrypt Streebog-512 + XTS 1024 bit',0,0),(29473,'VeraCrypt Streebog-512 + XTS 1536 bit',0,0),(29481,'VeraCrypt Streebog-512 + XTS 512 bit + boot-mode',0,0),(29482,'VeraCrypt Streebog-512 + XTS 1024 bit + boot-mode',0,0),(29483,'VeraCrypt Streebog-512 + XTS 1536 bit + boot-mode',0,0),(29511,'LUKS v1 SHA-1 + AES',0,1),(29512,'LUKS v1 SHA-1 + Serpent',0,1),(29513,'LUKS v1 SHA-1 + Twofish',0,1),(29521,'LUKS v1 SHA-256 + AES',0,1),(29522,'LUKS v1 SHA-256 + Serpent',0,1),(29523,'LUKS v1 SHA-256 + Twofish',0,1),(29531,'LUKS v1 SHA-512 + AES',0,1),(29532,'LUKS v1 SHA-512 + Serpent',0,1),(29533,'LUKS v1 SHA-512 + Twofish',0,1),(29541,'LUKS v1 RIPEMD-160 + AES',0,1),(29542,'LUKS v1 RIPEMD-160 + Serpent',0,1),(29543,'LUKS v1 RIPEMD-160 + Twofish',0,1),(29600,'Terra Station Wallet (AES256-CBC(PBKDF2($pass)))',0,1),(29700,'KeePass 1 (AES/Twofish) and KeePass 2 (AES) - keyfile only mode',0,1),(29800,'Bisq .wallet (scrypt)',0,1),(29910,'ENCsecurity Datavault (PBKDF2/no keychain)',0,1),(29920,'ENCsecurity Datavault (PBKDF2/keychain)',0,1),(29930,'ENCsecurity Datavault (MD5/no keychain)',0,1),(29940,'ENCsecurity Datavault (MD5/keychain)',0,1),(30000,'Python Werkzeug MD5 (HMAC-MD5 (key = $salt))',0,0),(30120,'Python Werkzeug SHA256 (HMAC-SHA256 (key = $salt))',0,0),(30420,'DANE RFC7929/RFC8162 SHA2-256',0,0),(30500,'md5(md5($salt).md5(md5($pass)))',1,0),(30600,'bcrypt(sha256($pass))',0,1),(30601,'bcrypt(HMAC-SHA256($pass))',0,1),(30700,'Anope IRC Services (enc_sha256)',0,0),(30901,'Bitcoin raw private key (P2PKH), compressed',0,0),(30902,'Bitcoin raw private key (P2PKH), uncompressed',0,0),(30903,'Bitcoin raw private key (P2WPKH, Bech32), compressed',0,0),(30904,'Bitcoin raw private key (P2WPKH, Bech32), uncompressed',0,0),(30905,'Bitcoin raw private key (P2SH(P2WPKH)), compressed',0,0),(30906,'Bitcoin raw private key (P2SH(P2WPKH)), uncompressed',0,0),(31000,'BLAKE2s-256',0,0),(31100,'ShangMi 3 (SM3)',0,0),(31200,'Veeam VBK',0,1),(31300,'MS SNTP',0,0),(31400,'SecureCRT MasterPassphrase v2',0,0),(31500,'Domain Cached Credentials (DCC), MS Cache (NT)',1,1),(31600,'Domain Cached Credentials 2 (DCC2), MS Cache 2, (NT)',0,1),(31700,'md5(md5(md5($pass).$salt1).$salt2)',1,0),(31800,'1Password, mobilekeychain (1Password 8)',0,1),(31900,'MetaMask Mobile Wallet',0,1),(32000,'NetIQ SSPR (MD5)',0,1),(32010,'NetIQ SSPR (SHA1)',0,1),(32020,'NetIQ SSPR (SHA-1 with Salt)',0,1),(32030,'NetIQ SSPR (SHA-256 with Salt)',0,1),(32031,'Adobe AEM (SSPR, SHA-256 with Salt)',0,1),(32040,'NetIQ SSPR (SHA-512 with Salt)',0,1),(32041,'Adobe AEM (SSPR, SHA-512 with Salt)',0,1),(32050,'NetIQ SSPR (PBKDF2WithHmacSHA1)',0,1),(32060,'NetIQ SSPR (PBKDF2WithHmacSHA256)',0,1),(32070,'NetIQ SSPR (PBKDF2WithHmacSHA512)',0,1),(32100,'Kerberos 5, etype 17, AS-REP',0,1),(32200,'Kerberos 5, etype 18, AS-REP',0,1),(32300,'Empire CMS (Admin password)',1,0),(32410,'sha512(sha512($pass).$salt)',1,0),(32420,'sha512(sha512_bin($pass).$salt)',1,0),(32500,'Dogechain.info Wallet',0,1),(32600,'CubeCart (whirlpool($salt.$pass.$salt))',1,0),(32700,'Kremlin Encrypt 3.0 w/NewDES',0,1),(32800,'md5(sha1(md5($pass)))',0,0),(32900,'PBKDF1-SHA1',1,1),(33000,'md5($salt1.$pass.$salt2)',1,0),(33100,'md5($salt.md5($pass).$salt)',1,0),(33300,'HMAC-BLAKE2S (key = $pass)',1,0),(33400,'mega.nz password-protected link (PBKDF2-HMAC-SHA512)',0,1),(33500,'RC4 40-bit DropN',0,0),(33501,'RC4 72-bit DropN',0,0),(33502,'RC4 104-bit DropN',0,0),(33600,'RIPEMD-320',0,0),(33650,'HMAC-RIPEMD320 (key = $pass)',1,0),(33660,'HMAC-RIPEMD320 (key = $salt)',1,0),(33700,'Microsoft Online Account (PBKDF2-HMAC-SHA256 + AES256)',0,1),(33800,'WBB4 (Woltlab Burning Board) [bcrypt(bcrypt($pass))]',0,1),(33900,'Citrix NetScaler (PBKDF2-HMAC-SHA256)',0,1),(34000,'Argon2',0,1),(34100,'LUKS v2 argon2 + SHA-256 + AES',0,1),(34200,'MurmurHash64A',1,0),(34201,'MurmurHash64A (zero seed)',0,0),(34211,'MurmurHash64A truncated (zero seed)',0,0),(34300,'KeePass (KDBX v4)',0,1),(34400,'sha224(sha224($pass))',0,0),(34500,'sha224(sha1($pass))',0,0),(34600,'MD6 (256)',0,0),(34700,'Blockchain, My Wallet, Legacy Wallets',0,0),(34800,'BLAKE2b-256',0,0),(34810,'BLAKE2b-256($pass.$salt)',1,0),(34820,'BLAKE2b-256($salt.$pass)',1,0),(35000,'SAP CODVN H (PWDSALTEDHASH) isSHA512',1,1),(35100,'sm3crypt $sm3$, SM3 (Unix)',1,1),(35200,'AS/400 SSHA1',1,0),(70000,'Argon2id [Bridged: reference implementation + tunings]',0,1),(70100,'scrypt [Bridged: Scrypt-Jane SMix]',0,1),(70200,'scrypt [Bridged: Scrypt-Yescrypt]',0,1),(72000,'Generic Hash [Bridged: Python Interpreter free-threading]',0,1),(73000,'Generic Hash [Bridged: Python Interpreter with GIL]',0,1),(99999,'Plaintext',0,0);
/*!40000 ALTER TABLE `HashType` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `Hashlist`
--

DROP TABLE IF EXISTS `Hashlist`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `Hashlist` (
  `hashlistId` int NOT NULL AUTO_INCREMENT,
  `hashlistName` varchar(100) NOT NULL,
  `format` int NOT NULL,
  `hashTypeId` int NOT NULL,
  `hashCount` int NOT NULL,
  `saltSeparator` varchar(10) DEFAULT NULL,
  `cracked` int NOT NULL,
  `isSecret` tinyint NOT NULL,
  `hexSalt` tinyint NOT NULL,
  `isSalted` tinyint NOT NULL,
  `accessGroupId` int NOT NULL,
  `notes` text NOT NULL,
  `brainId` int NOT NULL,
  `brainFeatures` tinyint NOT NULL,
  `isArchived` tinyint NOT NULL,
  PRIMARY KEY (`hashlistId`),
  KEY `hashTypeId` (`hashTypeId`),
  KEY `Hashlist_ibfk_2` (`accessGroupId`),
  KEY `isArchived` (`isArchived`,`hashlistId`),
  CONSTRAINT `Hashlist_ibfk_1` FOREIGN KEY (`hashTypeId`) REFERENCES `HashType` (`hashTypeId`),
  CONSTRAINT `Hashlist_ibfk_2` FOREIGN KEY (`accessGroupId`) REFERENCES `AccessGroup` (`accessGroupId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Hashlist`
--

LOCK TABLES `Hashlist` WRITE;
/*!40000 ALTER TABLE `Hashlist` DISABLE KEYS */;
/*!40000 ALTER TABLE `Hashlist` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `HashlistHashlist`
--

DROP TABLE IF EXISTS `HashlistHashlist`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `HashlistHashlist` (
  `hashlistHashlistId` int NOT NULL AUTO_INCREMENT,
  `parentHashlistId` int NOT NULL,
  `hashlistId` int NOT NULL,
  PRIMARY KEY (`hashlistHashlistId`),
  KEY `parentHashlistId` (`parentHashlistId`),
  KEY `hashlistId` (`hashlistId`),
  CONSTRAINT `HashlistHashlist_ibfk_1` FOREIGN KEY (`parentHashlistId`) REFERENCES `Hashlist` (`hashlistId`),
  CONSTRAINT `HashlistHashlist_ibfk_2` FOREIGN KEY (`hashlistId`) REFERENCES `Hashlist` (`hashlistId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `HashlistHashlist`
--

LOCK TABLES `HashlistHashlist` WRITE;
/*!40000 ALTER TABLE `HashlistHashlist` DISABLE KEYS */;
/*!40000 ALTER TABLE `HashlistHashlist` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `HealthCheck`
--

DROP TABLE IF EXISTS `HealthCheck`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `HealthCheck` (
  `healthCheckId` int NOT NULL AUTO_INCREMENT,
  `time` bigint NOT NULL,
  `status` int NOT NULL,
  `checkType` int NOT NULL,
  `hashtypeId` int NOT NULL,
  `crackerBinaryId` int NOT NULL,
  `expectedCracks` int NOT NULL,
  `attackCmd` text NOT NULL,
  PRIMARY KEY (`healthCheckId`),
  KEY `HealthCheck_ibfk_1` (`crackerBinaryId`),
  CONSTRAINT `HealthCheck_ibfk_1` FOREIGN KEY (`crackerBinaryId`) REFERENCES `CrackerBinary` (`crackerBinaryId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `HealthCheck`
--

LOCK TABLES `HealthCheck` WRITE;
/*!40000 ALTER TABLE `HealthCheck` DISABLE KEYS */;
/*!40000 ALTER TABLE `HealthCheck` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `HealthCheckAgent`
--

DROP TABLE IF EXISTS `HealthCheckAgent`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `HealthCheckAgent` (
  `healthCheckAgentId` int NOT NULL AUTO_INCREMENT,
  `healthCheckId` int NOT NULL,
  `agentId` int NOT NULL,
  `status` int NOT NULL,
  `cracked` int NOT NULL,
  `numGpus` int NOT NULL,
  `start` bigint NOT NULL,
  `htp_end` bigint NOT NULL,
  `errors` text NOT NULL,
  PRIMARY KEY (`healthCheckAgentId`),
  KEY `HealthCheckAgent_ibfk_1` (`agentId`),
  KEY `HealthCheckAgent_ibfk_2` (`healthCheckId`),
  CONSTRAINT `HealthCheckAgent_ibfk_1` FOREIGN KEY (`agentId`) REFERENCES `Agent` (`agentId`),
  CONSTRAINT `HealthCheckAgent_ibfk_2` FOREIGN KEY (`healthCheckId`) REFERENCES `HealthCheck` (`healthCheckId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `HealthCheckAgent`
--

LOCK TABLES `HealthCheckAgent` WRITE;
/*!40000 ALTER TABLE `HealthCheckAgent` DISABLE KEYS */;
/*!40000 ALTER TABLE `HealthCheckAgent` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `JwtApiKey`
--

DROP TABLE IF EXISTS `JwtApiKey`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `JwtApiKey` (
  `jwtApiKeyId` int NOT NULL AUTO_INCREMENT,
  `userId` int DEFAULT NULL,
  `startValid` bigint NOT NULL,
  `endValid` bigint NOT NULL,
  `isRevoked` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`jwtApiKeyId`),
  KEY `idx_jwtApiKey_userId` (`userId`),
  CONSTRAINT `fk_jwtApiKey_user` FOREIGN KEY (`userId`) REFERENCES `htp_User` (`userId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `JwtApiKey`
--

LOCK TABLES `JwtApiKey` WRITE;
/*!40000 ALTER TABLE `JwtApiKey` DISABLE KEYS */;
/*!40000 ALTER TABLE `JwtApiKey` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `LogEntry`
--

DROP TABLE IF EXISTS `LogEntry`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `LogEntry` (
  `logEntryId` bigint NOT NULL AUTO_INCREMENT,
  `issuer` varchar(50) NOT NULL,
  `issuerId` varchar(50) NOT NULL,
  `level` varchar(50) NOT NULL,
  `message` text NOT NULL,
  `time` bigint NOT NULL,
  PRIMARY KEY (`logEntryId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `LogEntry`
--

LOCK TABLES `LogEntry` WRITE;
/*!40000 ALTER TABLE `LogEntry` DISABLE KEYS */;
/*!40000 ALTER TABLE `LogEntry` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `NotificationSetting`
--

DROP TABLE IF EXISTS `NotificationSetting`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `NotificationSetting` (
  `notificationSettingId` int NOT NULL AUTO_INCREMENT,
  `action` varchar(50) NOT NULL,
  `objectId` int DEFAULT NULL,
  `notification` varchar(50) NOT NULL,
  `userId` int NOT NULL,
  `receiver` varchar(256) NOT NULL,
  `isActive` tinyint NOT NULL,
  PRIMARY KEY (`notificationSettingId`),
  KEY `userId` (`userId`),
  CONSTRAINT `NotificationSetting_ibfk_1` FOREIGN KEY (`userId`) REFERENCES `htp_User` (`userId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `NotificationSetting`
--

LOCK TABLES `NotificationSetting` WRITE;
/*!40000 ALTER TABLE `NotificationSetting` DISABLE KEYS */;
/*!40000 ALTER TABLE `NotificationSetting` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `Preprocessor`
--

DROP TABLE IF EXISTS `Preprocessor`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `Preprocessor` (
  `preprocessorId` int NOT NULL AUTO_INCREMENT,
  `name` varchar(256) NOT NULL,
  `url` varchar(512) NOT NULL,
  `binaryName` varchar(256) NOT NULL,
  `keyspaceCommand` varchar(256) DEFAULT NULL,
  `skipCommand` varchar(256) DEFAULT NULL,
  `limitCommand` varchar(256) DEFAULT NULL,
  PRIMARY KEY (`preprocessorId`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Preprocessor`
--

LOCK TABLES `Preprocessor` WRITE;
/*!40000 ALTER TABLE `Preprocessor` DISABLE KEYS */;
INSERT INTO `Preprocessor` VALUES (1,'Prince','https://github.com/hashcat/princeprocessor/releases/download/v0.22/princeprocessor-0.22.7z','pp','--keyspace','--skip','--limit');
/*!40000 ALTER TABLE `Preprocessor` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `Pretask`
--

DROP TABLE IF EXISTS `Pretask`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `Pretask` (
  `pretaskId` int NOT NULL AUTO_INCREMENT,
  `taskName` varchar(100) NOT NULL,
  `attackCmd` text NOT NULL,
  `chunkTime` int NOT NULL,
  `statusTimer` int NOT NULL,
  `color` varchar(20) DEFAULT NULL,
  `isSmall` tinyint NOT NULL,
  `isCpuTask` tinyint NOT NULL,
  `useNewBench` tinyint NOT NULL,
  `priority` int NOT NULL,
  `maxAgents` int NOT NULL,
  `isMaskImport` tinyint NOT NULL,
  `crackerBinaryTypeId` int NOT NULL,
  PRIMARY KEY (`pretaskId`),
  KEY `Pretask_ibfk_1` (`crackerBinaryTypeId`),
  CONSTRAINT `Pretask_ibfk_1` FOREIGN KEY (`crackerBinaryTypeId`) REFERENCES `CrackerBinaryType` (`crackerBinaryTypeId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Pretask`
--

LOCK TABLES `Pretask` WRITE;
/*!40000 ALTER TABLE `Pretask` DISABLE KEYS */;
/*!40000 ALTER TABLE `Pretask` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `RegVoucher`
--

DROP TABLE IF EXISTS `RegVoucher`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `RegVoucher` (
  `regVoucherId` int NOT NULL AUTO_INCREMENT,
  `voucher` varchar(100) NOT NULL,
  `time` bigint NOT NULL,
  PRIMARY KEY (`regVoucherId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `RegVoucher`
--

LOCK TABLES `RegVoucher` WRITE;
/*!40000 ALTER TABLE `RegVoucher` DISABLE KEYS */;
/*!40000 ALTER TABLE `RegVoucher` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `RightGroup`
--

DROP TABLE IF EXISTS `RightGroup`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `RightGroup` (
  `rightGroupId` int NOT NULL AUTO_INCREMENT,
  `groupName` varchar(50) NOT NULL,
  `permissions` text NOT NULL,
  PRIMARY KEY (`rightGroupId`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `RightGroup`
--

LOCK TABLES `RightGroup` WRITE;
/*!40000 ALTER TABLE `RightGroup` DISABLE KEYS */;
INSERT INTO `RightGroup` VALUES (1,'Administrator','ALL');
/*!40000 ALTER TABLE `RightGroup` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `Session`
--

DROP TABLE IF EXISTS `Session`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `Session` (
  `sessionId` int NOT NULL AUTO_INCREMENT,
  `userId` int NOT NULL,
  `sessionStartDate` bigint NOT NULL,
  `lastActionDate` bigint NOT NULL,
  `isOpen` tinyint NOT NULL,
  `sessionLifetime` int NOT NULL,
  `sessionKey` varchar(256) NOT NULL,
  PRIMARY KEY (`sessionId`),
  KEY `userId` (`userId`),
  CONSTRAINT `Session_ibfk_1` FOREIGN KEY (`userId`) REFERENCES `htp_User` (`userId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Session`
--

LOCK TABLES `Session` WRITE;
/*!40000 ALTER TABLE `Session` DISABLE KEYS */;
/*!40000 ALTER TABLE `Session` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `Speed`
--

DROP TABLE IF EXISTS `Speed`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `Speed` (
  `speedId` bigint NOT NULL AUTO_INCREMENT,
  `agentId` int NOT NULL,
  `taskId` int NOT NULL,
  `speed` bigint NOT NULL,
  `time` bigint NOT NULL,
  PRIMARY KEY (`speedId`),
  KEY `agentId` (`agentId`),
  KEY `taskId` (`taskId`),
  CONSTRAINT `Speed_ibfk_1` FOREIGN KEY (`agentId`) REFERENCES `Agent` (`agentId`),
  CONSTRAINT `Speed_ibfk_2` FOREIGN KEY (`taskId`) REFERENCES `Task` (`taskId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Speed`
--

LOCK TABLES `Speed` WRITE;
/*!40000 ALTER TABLE `Speed` DISABLE KEYS */;
/*!40000 ALTER TABLE `Speed` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `StoredValue`
--

DROP TABLE IF EXISTS `StoredValue`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `StoredValue` (
  `storedValueId` varchar(50) NOT NULL,
  `val` varchar(256) NOT NULL,
  PRIMARY KEY (`storedValueId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `StoredValue`
--

LOCK TABLES `StoredValue` WRITE;
/*!40000 ALTER TABLE `StoredValue` DISABLE KEYS */;
/*!40000 ALTER TABLE `StoredValue` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `Supertask`
--

DROP TABLE IF EXISTS `Supertask`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `Supertask` (
  `supertaskId` int NOT NULL AUTO_INCREMENT,
  `supertaskName` varchar(50) NOT NULL,
  PRIMARY KEY (`supertaskId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Supertask`
--

LOCK TABLES `Supertask` WRITE;
/*!40000 ALTER TABLE `Supertask` DISABLE KEYS */;
/*!40000 ALTER TABLE `Supertask` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `SupertaskPretask`
--

DROP TABLE IF EXISTS `SupertaskPretask`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `SupertaskPretask` (
  `supertaskPretaskId` int NOT NULL AUTO_INCREMENT,
  `supertaskId` int NOT NULL,
  `pretaskId` int NOT NULL,
  PRIMARY KEY (`supertaskPretaskId`),
  KEY `supertaskId` (`supertaskId`),
  KEY `pretaskId` (`pretaskId`),
  CONSTRAINT `SupertaskPretask_ibfk_1` FOREIGN KEY (`supertaskId`) REFERENCES `Supertask` (`supertaskId`),
  CONSTRAINT `SupertaskPretask_ibfk_2` FOREIGN KEY (`pretaskId`) REFERENCES `Pretask` (`pretaskId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `SupertaskPretask`
--

LOCK TABLES `SupertaskPretask` WRITE;
/*!40000 ALTER TABLE `SupertaskPretask` DISABLE KEYS */;
/*!40000 ALTER TABLE `SupertaskPretask` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `Task`
--

DROP TABLE IF EXISTS `Task`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `Task` (
  `taskId` int NOT NULL AUTO_INCREMENT,
  `taskName` varchar(256) NOT NULL,
  `attackCmd` text NOT NULL,
  `chunkTime` int NOT NULL,
  `statusTimer` int NOT NULL,
  `keyspace` bigint NOT NULL,
  `keyspaceProgress` bigint NOT NULL,
  `priority` int NOT NULL,
  `maxAgents` int NOT NULL,
  `color` varchar(20) DEFAULT NULL,
  `isSmall` tinyint NOT NULL,
  `isCpuTask` tinyint NOT NULL,
  `useNewBench` tinyint NOT NULL,
  `skipKeyspace` bigint NOT NULL,
  `crackerBinaryId` int DEFAULT NULL,
  `crackerBinaryTypeId` int DEFAULT NULL,
  `taskWrapperId` int NOT NULL,
  `isArchived` tinyint NOT NULL,
  `notes` text NOT NULL,
  `staticChunks` int NOT NULL,
  `chunkSize` bigint NOT NULL,
  `forcePipe` tinyint NOT NULL,
  `usePreprocessor` tinyint NOT NULL,
  `preprocessorCommand` varchar(256) NOT NULL,
  PRIMARY KEY (`taskId`),
  KEY `crackerBinaryId` (`crackerBinaryId`),
  KEY `Task_ibfk_2` (`crackerBinaryTypeId`),
  KEY `Task_ibfk_3` (`taskWrapperId`),
  KEY `isArchived_priority_taskId` (`isArchived`,`priority` DESC,`taskId`),
  CONSTRAINT `Task_ibfk_1` FOREIGN KEY (`crackerBinaryId`) REFERENCES `CrackerBinary` (`crackerBinaryId`),
  CONSTRAINT `Task_ibfk_2` FOREIGN KEY (`crackerBinaryTypeId`) REFERENCES `CrackerBinaryType` (`crackerBinaryTypeId`),
  CONSTRAINT `Task_ibfk_3` FOREIGN KEY (`taskWrapperId`) REFERENCES `TaskWrapper` (`taskWrapperId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Task`
--

LOCK TABLES `Task` WRITE;
/*!40000 ALTER TABLE `Task` DISABLE KEYS */;
/*!40000 ALTER TABLE `Task` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `TaskDebugOutput`
--

DROP TABLE IF EXISTS `TaskDebugOutput`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `TaskDebugOutput` (
  `taskDebugOutputId` int NOT NULL AUTO_INCREMENT,
  `taskId` int NOT NULL,
  `output` varchar(256) NOT NULL,
  PRIMARY KEY (`taskDebugOutputId`),
  KEY `TaskDebugOutput_ibfk_1` (`taskId`),
  CONSTRAINT `TaskDebugOutput_ibfk_1` FOREIGN KEY (`taskId`) REFERENCES `Task` (`taskId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `TaskDebugOutput`
--

LOCK TABLES `TaskDebugOutput` WRITE;
/*!40000 ALTER TABLE `TaskDebugOutput` DISABLE KEYS */;
/*!40000 ALTER TABLE `TaskDebugOutput` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `TaskWrapper`
--

DROP TABLE IF EXISTS `TaskWrapper`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `TaskWrapper` (
  `taskWrapperId` int NOT NULL AUTO_INCREMENT,
  `priority` int NOT NULL,
  `maxAgents` int NOT NULL,
  `taskType` int NOT NULL,
  `hashlistId` int NOT NULL,
  `accessGroupId` int DEFAULT NULL,
  `taskWrapperName` varchar(100) NOT NULL,
  `isArchived` tinyint NOT NULL,
  `cracked` int NOT NULL,
  PRIMARY KEY (`taskWrapperId`),
  KEY `hashlistId` (`hashlistId`),
  KEY `priority` (`priority`),
  KEY `accessGroupId` (`accessGroupId`),
  KEY `isArchived_priority_taskWrapperId` (`isArchived`,`priority` DESC,`taskWrapperId`),
  CONSTRAINT `TaskWrapper_ibfk_1` FOREIGN KEY (`hashlistId`) REFERENCES `Hashlist` (`hashlistId`),
  CONSTRAINT `TaskWrapper_ibfk_2` FOREIGN KEY (`accessGroupId`) REFERENCES `AccessGroup` (`accessGroupId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `TaskWrapper`
--

LOCK TABLES `TaskWrapper` WRITE;
/*!40000 ALTER TABLE `TaskWrapper` DISABLE KEYS */;
/*!40000 ALTER TABLE `TaskWrapper` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Temporary view structure for view `TaskWrapperDisplay`
--

DROP TABLE IF EXISTS `TaskWrapperDisplay`;
/*!50001 DROP VIEW IF EXISTS `TaskWrapperDisplay`*/;
SET @saved_cs_client     = @@character_set_client;
/*!50503 SET character_set_client = utf8mb4 */;
/*!50001 CREATE VIEW `TaskWrapperDisplay` AS SELECT 
 1 AS `taskWrapperId`,
 1 AS `taskWrapperPriority`,
 1 AS `taskWrapperMaxAgents`,
 1 AS `taskType`,
 1 AS `hashlistId`,
 1 AS `accessGroupId`,
 1 AS `taskWrapperName`,
 1 AS `taskWrapperIsArchived`,
 1 AS `cracked`,
 1 AS `taskId`,
 1 AS `taskName`,
 1 AS `attackCmd`,
 1 AS `chunkTime`,
 1 AS `statusTimer`,
 1 AS `keyspace`,
 1 AS `keyspaceProgress`,
 1 AS `taskPriority`,
 1 AS `taskMaxAgents`,
 1 AS `taskIsArchived`,
 1 AS `isSmall`,
 1 AS `isCpuTask`,
 1 AS `taskUsePreprocessor`,
 1 AS `displayName`,
 1 AS `hashlistName`,
 1 AS `hashCount`,
 1 AS `hashlistCracked`,
 1 AS `hashTypeId`,
 1 AS `hashTypeDescription`,
 1 AS `groupName`,
 1 AS `color`*/;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `Zap`
--

DROP TABLE IF EXISTS `Zap`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `Zap` (
  `zapId` int NOT NULL AUTO_INCREMENT,
  `hash` mediumtext NOT NULL,
  `solveTime` bigint NOT NULL,
  `agentId` int DEFAULT NULL,
  `hashlistId` int NOT NULL,
  PRIMARY KEY (`zapId`),
  KEY `agentId` (`agentId`),
  KEY `hashlistId` (`hashlistId`),
  CONSTRAINT `Zap_ibfk_1` FOREIGN KEY (`agentId`) REFERENCES `Agent` (`agentId`),
  CONSTRAINT `Zap_ibfk_2` FOREIGN KEY (`hashlistId`) REFERENCES `Hashlist` (`hashlistId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Zap`
--

LOCK TABLES `Zap` WRITE;
/*!40000 ALTER TABLE `Zap` DISABLE KEYS */;
/*!40000 ALTER TABLE `Zap` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `htp_User`
--

DROP TABLE IF EXISTS `htp_User`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `htp_User` (
  `userId` int NOT NULL AUTO_INCREMENT,
  `username` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `passwordHash` varchar(256) NOT NULL,
  `passwordSalt` varchar(256) NOT NULL,
  `isValid` tinyint NOT NULL,
  `isComputedPassword` tinyint NOT NULL,
  `lastLoginDate` bigint NOT NULL,
  `registeredSince` bigint NOT NULL,
  `sessionLifetime` int NOT NULL,
  `rightGroupId` int NOT NULL,
  `yubikey` varchar(256) DEFAULT NULL,
  `otp1` varchar(256) DEFAULT NULL,
  `otp2` varchar(256) DEFAULT NULL,
  `otp3` varchar(256) DEFAULT NULL,
  `otp4` varchar(256) DEFAULT NULL,
  PRIMARY KEY (`userId`),
  UNIQUE KEY `username` (`username`),
  KEY `rightGroupId` (`rightGroupId`),
  CONSTRAINT `User_ibfk_1` FOREIGN KEY (`rightGroupId`) REFERENCES `RightGroup` (`rightGroupId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `htp_User`
--

LOCK TABLES `htp_User` WRITE;
/*!40000 ALTER TABLE `htp_User` DISABLE KEYS */;
/*!40000 ALTER TABLE `htp_User` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Final view structure for view `TaskWrapperDisplay`
--

/*!50001 DROP VIEW IF EXISTS `TaskWrapperDisplay`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_0900_ai_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`hashtopolis`@`%` SQL SECURITY DEFINER */
/*!50001 VIEW `TaskWrapperDisplay` AS select `tw`.`taskWrapperId` AS `taskWrapperId`,`tw`.`priority` AS `taskWrapperPriority`,`tw`.`maxAgents` AS `taskWrapperMaxAgents`,`tw`.`taskType` AS `taskType`,`tw`.`hashlistId` AS `hashlistId`,`tw`.`accessGroupId` AS `accessGroupId`,`tw`.`taskWrapperName` AS `taskWrapperName`,`tw`.`isArchived` AS `taskWrapperIsArchived`,`tw`.`cracked` AS `cracked`,`t`.`taskId` AS `taskId`,`t`.`taskName` AS `taskName`,`t`.`attackCmd` AS `attackCmd`,`t`.`chunkTime` AS `chunkTime`,`t`.`statusTimer` AS `statusTimer`,`t`.`keyspace` AS `keyspace`,`t`.`keyspaceProgress` AS `keyspaceProgress`,`t`.`priority` AS `taskPriority`,`t`.`maxAgents` AS `taskMaxAgents`,`t`.`isArchived` AS `taskIsArchived`,`t`.`isSmall` AS `isSmall`,`t`.`isCpuTask` AS `isCpuTask`,`t`.`usePreprocessor` AS `taskUsePreprocessor`,(case when (`tw`.`taskType` = 0) then `t`.`taskName` else `tw`.`taskWrapperName` end) AS `displayName`,`h`.`hashlistName` AS `hashlistName`,`h`.`hashCount` AS `hashCount`,`h`.`cracked` AS `hashlistCracked`,`ht`.`hashTypeId` AS `hashTypeId`,`ht`.`description` AS `hashTypeDescription`,`ag`.`groupName` AS `groupName`,`t`.`color` AS `color` from ((((`TaskWrapper` `tw` left join `Task` `t` on(((`tw`.`taskType` = 0) and (`t`.`taskWrapperId` = `tw`.`taskWrapperId`)))) join `Hashlist` `h` on((`tw`.`hashlistId` = `h`.`hashlistId`))) join `HashType` `ht` on((`h`.`hashTypeId` = `ht`.`hashTypeId`))) join `AccessGroup` `ag` on((`tw`.`accessGroupId` = `ag`.`accessGroupId`))) */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
