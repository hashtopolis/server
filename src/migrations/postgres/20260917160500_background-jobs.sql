-- Create backgroundjob table for the cron-based background job queue.
CREATE TABLE backgroundjob (
    backgroundjobid integer NOT NULL,
    jobtype varchar(100) NOT NULL,
    payload json NOT NULL,
    status integer DEFAULT 0 NOT NULL,
    userid integer,
    createdat bigint NOT NULL,
    startedat bigint,
    finishedat bigint,
    exitcode integer,
    resultmessage varchar(1024)
);
CREATE SEQUENCE backgroundjob_backgroundjobid_seq AS integer START WITH 1 INCREMENT BY 1;
ALTER TABLE ONLY backgroundjob ALTER COLUMN backgroundjobid SET DEFAULT nextval('backgroundjob_backgroundjobid_seq'::regclass);
ALTER TABLE ONLY backgroundjob ADD CONSTRAINT backgroundjob_pkey PRIMARY KEY (backgroundjobid);
ALTER TABLE ONLY backgroundjob ADD CONSTRAINT backgroundjob_ibfk_1 FOREIGN KEY (userid) REFERENCES htp_user(userid);
CREATE INDEX backgroundjob_status_idx ON backgroundjob USING btree (status);
