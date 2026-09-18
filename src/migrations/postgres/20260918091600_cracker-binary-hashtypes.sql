-- Association table between cracker binaries and hashtypes: a binary only
-- supports the hashtypes it is linked to.
CREATE TABLE CrackerBinaryHashtype (
  crackerBinaryHashtypeId integer NOT NULL,
  crackerBinaryId integer NOT NULL,
  hashTypeId integer NOT NULL
);
CREATE SEQUENCE crackerbinaryhashtype_crackerbinaryhashtypeid_seq AS integer START WITH 1 INCREMENT BY 1;
ALTER TABLE ONLY CrackerBinaryHashtype ALTER COLUMN crackerBinaryHashtypeId SET DEFAULT nextval('crackerbinaryhashtype_crackerbinaryhashtypeid_seq'::regclass);
ALTER TABLE ONLY CrackerBinaryHashtype ADD CONSTRAINT crackerbinaryhashtype_pkey PRIMARY KEY (crackerBinaryHashtypeId);
CREATE INDEX IF NOT EXISTS crackerbinaryhashtype_crackerbinaryid_idx ON CrackerBinaryHashtype USING btree (crackerBinaryId);
CREATE INDEX IF NOT EXISTS crackerbinaryhashtype_hashtypeid_idx ON CrackerBinaryHashtype USING btree (hashTypeId);
ALTER TABLE ONLY CrackerBinaryHashtype ADD CONSTRAINT crackerbinaryhashtype_ibfk_1 FOREIGN KEY (crackerBinaryId) REFERENCES CrackerBinary(crackerBinaryId);
ALTER TABLE ONLY CrackerBinaryHashtype ADD CONSTRAINT crackerbinaryhashtype_ibfk_2 FOREIGN KEY (hashTypeId) REFERENCES HashType(hashTypeId);

-- Transition for existing binaries: hashcat binaries support every hashtype
-- (they follow the hashcat mode numbering), so they are linked to all existing
-- hashtypes. Binaries of other types start without any association, their
-- supported hashtypes have to be associated manually.
INSERT INTO CrackerBinaryHashtype (crackerBinaryId, hashTypeId)
  SELECT b.crackerBinaryId, h.hashTypeId
  FROM CrackerBinary b
  JOIN CrackerBinaryType t ON b.crackerBinaryTypeId = t.crackerBinaryTypeId AND t.typeName = 'hashcat'
  CROSS JOIN HashType h;
