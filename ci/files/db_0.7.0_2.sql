--
-- Indexes for table `ApiKey`
--
ALTER TABLE `ApiKey`
  ADD PRIMARY KEY (`apiKeyId`);

ALTER TABLE `ApiGroup`
  ADD PRIMARY KEY (`apiGroupId`);


--
-- Indizes der exportierten Tabellen
--

--
-- Indizes für die Tabelle `AccessGroup`
--
ALTER TABLE `AccessGroup`
  ADD PRIMARY KEY (`accessGroupId`);

--
-- Indizes für die Tabelle `AccessGroupAgent`
--
ALTER TABLE `AccessGroupAgent`
  ADD PRIMARY KEY (`accessGroupAgentId`),
  ADD KEY `accessGroupId` (`accessGroupId`),
  ADD KEY `agentId` (`agentId`);

--
-- Indizes für die Tabelle `AccessGroupUser`
--
ALTER TABLE `AccessGroupUser`
  ADD PRIMARY KEY (`accessGroupUserId`),
  ADD KEY `accessGroupId` (`accessGroupId`),
  ADD KEY `userId` (`userId`);

--
-- Indizes für die Tabelle `Agent`
--
ALTER TABLE `Agent`
  ADD PRIMARY KEY (`agentId`),
  ADD KEY `userId` (`userId`);

--
-- Indizes für die Tabelle `AgentBinary`
--
ALTER TABLE `AgentBinary`
  ADD PRIMARY KEY (`agentBinaryId`);

--
-- Indizes für die Tabelle `AgentError`
--
ALTER TABLE `AgentError`
  ADD PRIMARY KEY (`agentErrorId`),
  ADD KEY `agentId` (`agentId`),
  ADD KEY `taskId` (`taskId`);

--
-- Indizes für die Tabelle `AgentStat`
--
ALTER TABLE `AgentStat`
  ADD PRIMARY KEY (`agentStatId`),
  ADD KEY `agentId` (`agentId`);

--
-- Indizes für die Tabelle `AgentZap`
--
ALTER TABLE `AgentZap`
  ADD PRIMARY KEY (`agentZapId`),
  ADD KEY `agentId` (`agentId`),
  ADD KEY `lastZapId` (`lastZapId`);

--
-- Indizes für die Tabelle `Assignment`
--
ALTER TABLE `Assignment`
  ADD PRIMARY KEY (`assignmentId`),
  ADD KEY `taskId` (`taskId`),
  ADD KEY `agentId` (`agentId`);

--
-- Indizes für die Tabelle `Chunk`
--
ALTER TABLE `Chunk`
  ADD PRIMARY KEY (`chunkId`),
  ADD KEY `taskId` (`taskId`),
  ADD KEY `agentId` (`agentId`);

--
-- Indizes für die Tabelle `Config`
--
ALTER TABLE `Config`
  ADD PRIMARY KEY (`configId`),
  ADD KEY `configSectionId` (`configSectionId`);

--
-- Indizes für die Tabelle `ConfigSection`
--
ALTER TABLE `ConfigSection`
  ADD PRIMARY KEY (`configSectionId`);

--
-- Indizes für die Tabelle `CrackerBinary`
--
ALTER TABLE `CrackerBinary`
  ADD PRIMARY KEY (`crackerBinaryId`),
  ADD KEY `crackerBinaryTypeId` (`crackerBinaryTypeId`);

--
-- Indizes für die Tabelle `CrackerBinaryType`
--
ALTER TABLE `CrackerBinaryType`
  ADD PRIMARY KEY (`crackerBinaryTypeId`);

--
-- Indizes für die Tabelle `File`
--
ALTER TABLE `File`
  ADD PRIMARY KEY (`fileId`);

--
-- Indizes für die Tabelle `FilePretask`
--
ALTER TABLE `FilePretask`
  ADD PRIMARY KEY (`filePretaskId`),
  ADD KEY `fileId` (`fileId`),
  ADD KEY `pretaskId` (`pretaskId`);

--
-- Indizes für die Tabelle `FileTask`
--
ALTER TABLE `FileTask`
  ADD PRIMARY KEY (`fileTaskId`),
  ADD KEY `fileId` (`fileId`),
  ADD KEY `taskId` (`taskId`);

--
-- Indizes für die Tabelle `Hash`
--
ALTER TABLE `Hash`
  ADD PRIMARY KEY (`hashId`),
  ADD KEY `hashlistId` (`hashlistId`),
  ADD KEY `chunkId` (`chunkId`),
  ADD KEY `isCracked` (`isCracked`),
  ADD KEY `hash` (`hash`);

--
-- Indizes für die Tabelle `HashBinary`
--
ALTER TABLE `HashBinary`
  ADD PRIMARY KEY (`hashBinaryId`),
  ADD KEY `hashlistId` (`hashlistId`),
  ADD KEY `chunkId` (`chunkId`);

--
-- Indizes für die Tabelle `Hashlist`
--
ALTER TABLE `Hashlist`
  ADD PRIMARY KEY (`hashlistId`),
  ADD KEY `hashTypeId` (`hashTypeId`);

--
-- Indizes für die Tabelle `HashlistHashlist`
--
ALTER TABLE `HashlistHashlist`
  ADD PRIMARY KEY (`hashlistHashlistId`),
  ADD KEY `parentHashlistId` (`parentHashlistId`),
  ADD KEY `hashlistId` (`hashlistId`);

--
-- Indizes für die Tabelle `HashType`
--
ALTER TABLE `HashType`
  ADD PRIMARY KEY (`hashTypeId`);

--
-- Indizes für die Tabelle `LogEntry`
--
ALTER TABLE `LogEntry`
  ADD PRIMARY KEY (`logEntryId`);

--
-- Indizes für die Tabelle `NotificationSetting`
--
ALTER TABLE `NotificationSetting`
  ADD PRIMARY KEY (`notificationSettingId`),
  ADD KEY `userId` (`userId`);

--
-- Indizes für die Tabelle `Pretask`
--
ALTER TABLE `Pretask`
  ADD PRIMARY KEY (`pretaskId`);

--
-- Indizes für die Tabelle `RegVoucher`
--
ALTER TABLE `RegVoucher`
  ADD PRIMARY KEY (`regVoucherId`);

--
-- Indizes für die Tabelle `RightGroup`
--
ALTER TABLE `RightGroup`
  ADD PRIMARY KEY (`rightGroupId`);

--
-- Indizes für die Tabelle `Session`
--
ALTER TABLE `Session`
  ADD PRIMARY KEY (`sessionId`),
  ADD KEY `userId` (`userId`);

--
-- Indizes für die Tabelle `StoredValue`
--
ALTER TABLE `StoredValue`
  ADD PRIMARY KEY (`storedValueId`);

--
-- Indizes für die Tabelle `Supertask`
--
ALTER TABLE `Supertask`
  ADD PRIMARY KEY (`supertaskId`);

--
-- Indizes für die Tabelle `SupertaskPretask`
--
ALTER TABLE `SupertaskPretask`
  ADD PRIMARY KEY (`supertaskPretaskId`),
  ADD KEY `supertaskId` (`supertaskId`),
  ADD KEY `pretaskId` (`pretaskId`);

--
-- Indizes für die Tabelle `Task`
--
ALTER TABLE `Task`
  ADD PRIMARY KEY (`taskId`),
  ADD KEY `crackerBinaryId` (`crackerBinaryId`);

--
-- Indizes für die Tabelle `TaskWrapper`
--
ALTER TABLE `TaskWrapper`
  ADD PRIMARY KEY (`taskWrapperId`),
  ADD KEY `hashlistId` (`hashlistId`),
  ADD KEY `accessGroupId` (`accessGroupId`);

--
-- Indizes für die Tabelle `User`
--
ALTER TABLE `User`
  ADD PRIMARY KEY (`userId`),
  ADD UNIQUE KEY `username` (`username`),
  ADD KEY `rightGroupId` (`rightGroupId`);

--
-- Indizes für die Tabelle `Zap`
--
ALTER TABLE `Zap`
  ADD PRIMARY KEY (`zapId`),
  ADD KEY `agentId` (`agentId`),
  ADD KEY `hashlistId` (`hashlistId`);

--
-- AUTO_INCREMENT für exportierte Tabellen
--

ALTER TABLE `ApiKey`
  MODIFY `apiKeyId` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `ApiGroup`
  MODIFY `apiGroupId` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `AccessGroup`
--
ALTER TABLE `AccessGroup`
  MODIFY `accessGroupId` INT(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT für Tabelle `AccessGroupAgent`
--
ALTER TABLE `AccessGroupAgent`
  MODIFY `accessGroupAgentId` INT(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT für Tabelle `AccessGroupUser`
--
ALTER TABLE `AccessGroupUser`
  MODIFY `accessGroupUserId` INT(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT für Tabelle `Agent`
--
ALTER TABLE `Agent`
  MODIFY `agentId` INT(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT für Tabelle `AgentBinary`
--
ALTER TABLE `AgentBinary`
  MODIFY `agentBinaryId` INT(11) NOT NULL AUTO_INCREMENT,
  AUTO_INCREMENT = 3;
--
-- AUTO_INCREMENT für Tabelle `AgentError`
--
ALTER TABLE `AgentError`
  MODIFY `agentErrorId` INT(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT für Tabelle `AgentStat`
--
ALTER TABLE `AgentStat`
  MODIFY `agentStatId` INT(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT für Tabelle `AgentZap`
--
ALTER TABLE `AgentZap`
  MODIFY `agentZapId` INT(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT für Tabelle `Assignment`
--
ALTER TABLE `Assignment`
  MODIFY `assignmentId` INT(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT für Tabelle `Chunk`
--
ALTER TABLE `Chunk`
  MODIFY `chunkId` INT(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT für Tabelle `Config`
--
ALTER TABLE `Config`
  MODIFY `configId` INT(11) NOT NULL AUTO_INCREMENT,
  AUTO_INCREMENT = 22;
--
-- AUTO_INCREMENT für Tabelle `ConfigSection`
--
ALTER TABLE `ConfigSection`
  MODIFY `configSectionId` INT(11) NOT NULL AUTO_INCREMENT,
  AUTO_INCREMENT = 5;
--
-- AUTO_INCREMENT für Tabelle `CrackerBinary`
--
ALTER TABLE `CrackerBinary`
  MODIFY `crackerBinaryId` INT(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT für Tabelle `CrackerBinaryType`
--
ALTER TABLE `CrackerBinaryType`
  MODIFY `crackerBinaryTypeId` INT(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT für Tabelle `File`
--
ALTER TABLE `File`
  MODIFY `fileId` INT(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT für Tabelle `FilePretask`
--
ALTER TABLE `FilePretask`
  MODIFY `filePretaskId` INT(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT für Tabelle `FileTask`
--
ALTER TABLE `FileTask`
  MODIFY `fileTaskId` INT(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT für Tabelle `Hash`
--
ALTER TABLE `Hash`
  MODIFY `hashId` INT(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT für Tabelle `HashBinary`
--
ALTER TABLE `HashBinary`
  MODIFY `hashBinaryId` INT(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT für Tabelle `Hashlist`
--
ALTER TABLE `Hashlist`
  MODIFY `hashlistId` INT(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT für Tabelle `HashlistHashlist`
--
ALTER TABLE `HashlistHashlist`
  MODIFY `hashlistHashlistId` INT(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT für Tabelle `LogEntry`
--
ALTER TABLE `LogEntry`
  MODIFY `logEntryId` INT(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT für Tabelle `NotificationSetting`
--
ALTER TABLE `NotificationSetting`
  MODIFY `notificationSettingId` INT(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT für Tabelle `Pretask`
--
ALTER TABLE `Pretask`
  MODIFY `pretaskId` INT(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT für Tabelle `RegVoucher`
--
ALTER TABLE `RegVoucher`
  MODIFY `regVoucherId` INT(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT für Tabelle `RightGroup`
--
ALTER TABLE `RightGroup`
  MODIFY `rightGroupId` INT(11) NOT NULL AUTO_INCREMENT,
  AUTO_INCREMENT = 6;
--
-- AUTO_INCREMENT für Tabelle `Session`
--
ALTER TABLE `Session`
  MODIFY `sessionId` INT(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT für Tabelle `Supertask`
--
ALTER TABLE `Supertask`
  MODIFY `supertaskId` INT(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT für Tabelle `SupertaskPretask`
--
ALTER TABLE `SupertaskPretask`
  MODIFY `supertaskPretaskId` INT(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT für Tabelle `Task`
--
ALTER TABLE `Task`
  MODIFY `taskId` INT(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT für Tabelle `TaskWrapper`
--
ALTER TABLE `TaskWrapper`
  MODIFY `taskWrapperId` INT(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT für Tabelle `User`
--
ALTER TABLE `User`
  MODIFY `userId` INT(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT für Tabelle `Zap`
--
ALTER TABLE `Zap`
  MODIFY `zapId` INT(11) NOT NULL AUTO_INCREMENT;
--
-- Constraints der exportierten Tabellen
--

--
-- Constraints der Tabelle `AccessGroupAgent`
--
ALTER TABLE `AccessGroupAgent`
  ADD CONSTRAINT `AccessGroupAgent_ibfk_1` FOREIGN KEY (`accessGroupId`) REFERENCES `AccessGroup` (`accessGroupId`),
  ADD CONSTRAINT `AccessGroupAgent_ibfk_2` FOREIGN KEY (`agentId`) REFERENCES `Agent` (`agentId`);

--
-- Constraints der Tabelle `AccessGroupUser`
--
ALTER TABLE `AccessGroupUser`
  ADD CONSTRAINT `AccessGroupUser_ibfk_1` FOREIGN KEY (`accessGroupId`) REFERENCES `AccessGroup` (`accessGroupId`),
  ADD CONSTRAINT `AccessGroupUser_ibfk_2` FOREIGN KEY (`userId`) REFERENCES `User` (`userId`);

--
-- Constraints der Tabelle `Agent`
--
ALTER TABLE `Agent`
  ADD CONSTRAINT `Agent_ibfk_1` FOREIGN KEY (`userId`) REFERENCES `User` (`userId`);

--
-- Constraints der Tabelle `AgentError`
--
ALTER TABLE `AgentError`
  ADD CONSTRAINT `AgentError_ibfk_1` FOREIGN KEY (`agentId`) REFERENCES `Agent` (`agentId`),
  ADD CONSTRAINT `AgentError_ibfk_2` FOREIGN KEY (`taskId`) REFERENCES `Task` (`taskId`);

--
-- Constraints der Tabelle `AgentStat`
--
ALTER TABLE `AgentStat`
  ADD CONSTRAINT `AgentStat_ibfk_1` FOREIGN KEY (`agentId`) REFERENCES `Agent` (`agentId`);

--
-- Constraints der Tabelle `AgentZap`
--
ALTER TABLE `AgentZap`
  ADD CONSTRAINT `AgentZap_ibfk_1` FOREIGN KEY (`agentId`) REFERENCES `Agent` (`agentId`),
  ADD CONSTRAINT `AgentZap_ibfk_2` FOREIGN KEY (`lastZapId`) REFERENCES `Zap` (`zapId`);

--
-- Constraints der Tabelle `Assignment`
--
ALTER TABLE `Assignment`
  ADD CONSTRAINT `Assignment_ibfk_1` FOREIGN KEY (`taskId`) REFERENCES `Task` (`taskId`),
  ADD CONSTRAINT `Assignment_ibfk_2` FOREIGN KEY (`agentId`) REFERENCES `Agent` (`agentId`);

--
-- Constraints der Tabelle `Chunk`
--
ALTER TABLE `Chunk`
  ADD CONSTRAINT `Chunk_ibfk_1` FOREIGN KEY (`taskId`) REFERENCES `Task` (`taskId`),
  ADD CONSTRAINT `Chunk_ibfk_2` FOREIGN KEY (`agentId`) REFERENCES `Agent` (`agentId`);

--
-- Constraints der Tabelle `Config`
--
ALTER TABLE `Config`
  ADD CONSTRAINT `Config_ibfk_1` FOREIGN KEY (`configSectionId`) REFERENCES `ConfigSection` (`configSectionId`);

--
-- Constraints der Tabelle `CrackerBinary`
--
ALTER TABLE `CrackerBinary`
  ADD CONSTRAINT `CrackerBinary_ibfk_1` FOREIGN KEY (`crackerBinaryTypeId`) REFERENCES `CrackerBinaryType` (`crackerBinaryTypeId`);

--
-- Constraints der Tabelle `FilePretask`
--
ALTER TABLE `FilePretask`
  ADD CONSTRAINT `FilePretask_ibfk_1` FOREIGN KEY (`fileId`) REFERENCES `File` (`fileId`),
  ADD CONSTRAINT `FilePretask_ibfk_2` FOREIGN KEY (`pretaskId`) REFERENCES `Pretask` (`pretaskId`);

--
-- Constraints der Tabelle `FileTask`
--
ALTER TABLE `FileTask`
  ADD CONSTRAINT `FileTask_ibfk_1` FOREIGN KEY (`fileId`) REFERENCES `File` (`fileId`),
  ADD CONSTRAINT `FileTask_ibfk_2` FOREIGN KEY (`taskId`) REFERENCES `Task` (`taskId`);

--
-- Constraints der Tabelle `Hash`
--
ALTER TABLE `Hash`
  ADD CONSTRAINT `Hash_ibfk_1` FOREIGN KEY (`hashlistId`) REFERENCES `Hashlist` (`hashlistId`),
  ADD CONSTRAINT `Hash_ibfk_2` FOREIGN KEY (`chunkId`) REFERENCES `Chunk` (`chunkId`);

--
-- Constraints der Tabelle `HashBinary`
--
ALTER TABLE `HashBinary`
  ADD CONSTRAINT `HashBinary_ibfk_1` FOREIGN KEY (`hashlistId`) REFERENCES `Hashlist` (`hashlistId`),
  ADD CONSTRAINT `HashBinary_ibfk_2` FOREIGN KEY (`chunkId`) REFERENCES `Chunk` (`chunkId`);

--
-- Constraints der Tabelle `Hashlist`
--
ALTER TABLE `Hashlist`
  ADD CONSTRAINT `Hashlist_ibfk_1` FOREIGN KEY (`hashTypeId`) REFERENCES `HashType` (`hashTypeId`);

--
-- Constraints der Tabelle `HashlistHashlist`
--
ALTER TABLE `HashlistHashlist`
  ADD CONSTRAINT `HashlistHashlist_ibfk_1` FOREIGN KEY (`parentHashlistId`) REFERENCES `Hashlist` (`hashlistId`),
  ADD CONSTRAINT `HashlistHashlist_ibfk_2` FOREIGN KEY (`hashlistId`) REFERENCES `Hashlist` (`hashlistId`);

--
-- Constraints der Tabelle `NotificationSetting`
--
ALTER TABLE `NotificationSetting`
  ADD CONSTRAINT `NotificationSetting_ibfk_1` FOREIGN KEY (`userId`) REFERENCES `User` (`userId`);

--
-- Constraints der Tabelle `Session`
--
ALTER TABLE `Session`
  ADD CONSTRAINT `Session_ibfk_1` FOREIGN KEY (`userId`) REFERENCES `User` (`userId`);

--
-- Constraints der Tabelle `SupertaskPretask`
--
ALTER TABLE `SupertaskPretask`
  ADD CONSTRAINT `SupertaskPretask_ibfk_1` FOREIGN KEY (`supertaskId`) REFERENCES `Supertask` (`supertaskId`),
  ADD CONSTRAINT `SupertaskPretask_ibfk_2` FOREIGN KEY (`pretaskId`) REFERENCES `Pretask` (`pretaskId`);

--
-- Constraints der Tabelle `Task`
--
ALTER TABLE `Task`
  ADD CONSTRAINT `Task_ibfk_1` FOREIGN KEY (`crackerBinaryId`) REFERENCES `CrackerBinary` (`crackerBinaryId`);

--
-- Constraints der Tabelle `TaskWrapper`
--
ALTER TABLE `TaskWrapper`
  ADD CONSTRAINT `TaskWrapper_ibfk_1` FOREIGN KEY (`hashlistId`) REFERENCES `Hashlist` (`hashlistId`),
  ADD CONSTRAINT `TaskWrapper_ibfk_2` FOREIGN KEY (`accessGroupId`) REFERENCES `AccessGroup` (`accessGroupId`);

--
-- Constraints der Tabelle `User`
--
ALTER TABLE `User`
  ADD CONSTRAINT `User_ibfk_1` FOREIGN KEY (`rightGroupId`) REFERENCES `RightGroup` (`rightGroupId`);

--
-- Constraints der Tabelle `Zap`
--
ALTER TABLE `Zap`
  ADD CONSTRAINT `Zap_ibfk_1` FOREIGN KEY (`agentId`) REFERENCES `Agent` (`agentId`),
  ADD CONSTRAINT `Zap_ibfk_2` FOREIGN KEY (`hashlistId`) REFERENCES `Hashlist` (`hashlistId`);

/*!40101 SET CHARACTER_SET_CLIENT = @OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS = @OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION = @OLD_COLLATION_CONNECTION */;