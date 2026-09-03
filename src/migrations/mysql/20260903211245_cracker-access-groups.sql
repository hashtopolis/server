-- Cracker binaries and types belong to an access group: all binaries of a type are in
-- the same group as the type. Existing rows are assigned to the default access group.
ALTER TABLE CrackerBinary ADD COLUMN accessGroupId int NOT NULL DEFAULT 1 AFTER filename;
ALTER TABLE CrackerBinary ADD KEY `accessGroupId` (`accessGroupId`);
ALTER TABLE CrackerBinary ADD CONSTRAINT `CrackerBinary_ibfk_2` FOREIGN KEY (`accessGroupId`) REFERENCES `AccessGroup` (`accessGroupId`);

ALTER TABLE CrackerBinaryType ADD COLUMN accessGroupId int NOT NULL DEFAULT 1 AFTER isChunkingAvailable;
ALTER TABLE CrackerBinaryType ADD KEY `accessGroupId` (`accessGroupId`);
ALTER TABLE CrackerBinaryType ADD CONSTRAINT `CrackerBinaryType_ibfk_2` FOREIGN KEY (`accessGroupId`) REFERENCES `AccessGroup` (`accessGroupId`);

-- the access group must be provided explicitly, no silent default for new rows
ALTER TABLE CrackerBinary ALTER accessGroupId DROP DEFAULT;
ALTER TABLE CrackerBinaryType ALTER accessGroupId DROP DEFAULT;
