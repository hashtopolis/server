-- Association table between cracker binaries and hashtypes: a binary only
-- supports the hashtypes it is linked to. Each pair is only linked once,
-- the unique constraint also protects against concurrent requests creating
-- the same association twice.
CREATE TABLE CrackerBinaryHashtype (
  crackerBinaryHashtypeId integer NOT NULL,
  crackerBinaryId integer NOT NULL,
  hashTypeId integer NOT NULL
);
CREATE SEQUENCE crackerbinaryhashtype_crackerbinaryhashtypeid_seq AS integer START WITH 1 INCREMENT BY 1;
ALTER TABLE ONLY CrackerBinaryHashtype ALTER COLUMN crackerBinaryHashtypeId SET DEFAULT nextval('crackerbinaryhashtype_crackerbinaryhashtypeid_seq'::regclass);
ALTER TABLE ONLY CrackerBinaryHashtype ADD CONSTRAINT crackerbinaryhashtype_pkey PRIMARY KEY (crackerBinaryHashtypeId);
ALTER TABLE ONLY CrackerBinaryHashtype ADD CONSTRAINT crackerbinaryhashtype_crackerbinaryid_hashtypeid_key UNIQUE (crackerBinaryId, hashTypeId);
CREATE INDEX IF NOT EXISTS crackerbinaryhashtype_hashtypeid_idx ON CrackerBinaryHashtype USING btree (hashTypeId);
ALTER TABLE ONLY CrackerBinaryHashtype ADD CONSTRAINT crackerbinaryhashtype_ibfk_1 FOREIGN KEY (crackerBinaryId) REFERENCES CrackerBinary(crackerBinaryId);
ALTER TABLE ONLY CrackerBinaryHashtype ADD CONSTRAINT crackerbinaryhashtype_ibfk_2 FOREIGN KEY (hashTypeId) REFERENCES HashType(hashTypeId);

-- Transition for existing binaries: hashcat binaries get their hashtype
-- associations populated by a scan of the binary, which reads the supported
-- hash-modes from the unpacked archive. A pending scan job is enqueued for
-- each of them here, the next run of the background job runner executes the
-- scans. Binaries of other types start without any association, their
-- supported hashtypes have to be associated manually.
INSERT INTO BackgroundJob (jobType, payload, status, "userId", createdAt)
  SELECT 'scan_cracker',
         json_build_object('crackerBinaryId', b.crackerBinaryId),
         0, NULL, extract(epoch from now())
  FROM CrackerBinary b
  JOIN CrackerBinaryType t ON b.crackerBinaryTypeId = t.crackerBinaryTypeId AND t.typeName = 'hashcat';
