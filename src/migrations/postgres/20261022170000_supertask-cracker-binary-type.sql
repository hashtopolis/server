-- A supertask belongs to exactly one cracker binary type, all of its pretasks
-- are of that type. The type is stored on the supertask itself instead of
-- being derived from the pretasks. Existing rows are assigned to type 1
-- (hashcat), a backfill from the pretasks of existing supertasks is pending.
ALTER TABLE Supertask ADD COLUMN crackerBinaryTypeId INT NOT NULL DEFAULT 1;
CREATE INDEX IF NOT EXISTS supertask_crackerbinarytypeid_idx ON Supertask(crackerBinaryTypeId);
ALTER TABLE ONLY Supertask ADD CONSTRAINT supertask_ibfk_1 FOREIGN KEY (crackerBinaryTypeId) REFERENCES CrackerBinaryType(crackerBinaryTypeId);

-- the type must be provided explicitly, no silent default for new rows
ALTER TABLE ONLY Supertask ALTER COLUMN crackerBinaryTypeId DROP DEFAULT;
