-- Cracker binaries and types belong to an access group: all binaries of a type are in
-- the same group as the type. Existing rows are assigned to the default access group.
ALTER TABLE CrackerBinary ADD COLUMN accessGroupId INT NOT NULL DEFAULT 1;
ALTER TABLE CrackerBinaryType ADD COLUMN accessGroupId INT NOT NULL DEFAULT 1;

CREATE INDEX IF NOT EXISTS crackerbinary_accessgroupid_idx ON CrackerBinary(accessGroupId);
CREATE INDEX IF NOT EXISTS crackerbinarytype_accessgroupid_idx ON CrackerBinaryType(accessGroupId);

ALTER TABLE ONLY CrackerBinary ADD CONSTRAINT crackerbinary_ibfk_2 FOREIGN KEY (accessGroupId) REFERENCES AccessGroup(accessGroupId);
ALTER TABLE ONLY CrackerBinaryType ADD CONSTRAINT crackerbinarytype_ibfk_2 FOREIGN KEY (accessGroupId) REFERENCES AccessGroup(accessGroupId);

-- the access group must be provided explicitly, no silent default for new rows
ALTER TABLE CrackerBinary ALTER COLUMN accessGroupId DROP DEFAULT;
ALTER TABLE CrackerBinaryType ALTER COLUMN accessGroupId DROP DEFAULT;
