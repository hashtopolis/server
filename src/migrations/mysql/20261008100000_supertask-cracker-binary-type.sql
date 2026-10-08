-- A supertask belongs to exactly one cracker binary type, all of its pretasks
-- are of that type. The type is stored on the supertask itself instead of
-- being derived from the pretasks. Existing rows are assigned to type 1
-- (hashcat), a backfill from the pretasks of existing supertasks is pending.
ALTER TABLE Supertask ADD COLUMN crackerBinaryTypeId int NOT NULL DEFAULT 1 AFTER supertaskName;
ALTER TABLE Supertask ADD KEY `crackerBinaryTypeId` (`crackerBinaryTypeId`);
ALTER TABLE Supertask ADD CONSTRAINT `Supertask_ibfk_1` FOREIGN KEY (`crackerBinaryTypeId`) REFERENCES `CrackerBinaryType` (`crackerBinaryTypeId`);

-- the type must be provided explicitly, no silent default for new rows
ALTER TABLE Supertask ALTER crackerBinaryTypeId DROP DEFAULT;
