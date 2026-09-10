-- Cracker binaries belong to an access group. Existing rows are assigned to the
-- default access group.
ALTER TABLE CrackerBinary ADD COLUMN accessGroupId INT NOT NULL DEFAULT 1;
CREATE INDEX IF NOT EXISTS crackerbinary_accessgroupid_idx ON CrackerBinary(accessGroupId);
ALTER TABLE ONLY CrackerBinary ADD CONSTRAINT crackerbinary_ibfk_2 FOREIGN KEY (accessGroupId) REFERENCES AccessGroup(accessGroupId);

-- the access group must be provided explicitly, no silent default for new rows
ALTER TABLE CrackerBinary ALTER COLUMN accessGroupId DROP DEFAULT;
