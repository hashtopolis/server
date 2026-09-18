-- Refresh tokens: long-lived, single-use credentials that are exchanged for short-lived access tokens.
-- Only the SHA-256 hash of the token string is stored. Tokens rotate on every use; all rotations of one
-- login session share a familyid so a replayed token can revoke the entire session.
CREATE TABLE refreshtoken (
  refreshtokenid integer GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
  userid integer NOT NULL,
  tokenhash varchar(64) NOT NULL,
  familyid varchar(32) NOT NULL,
  issuedat bigint NOT NULL,
  endvalid bigint NOT NULL,
  usedat bigint DEFAULT NULL,
  isrevoked boolean DEFAULT false NOT NULL,
  CONSTRAINT uq_refreshtoken_tokenhash UNIQUE (tokenhash),
  CONSTRAINT refreshtoken_user_fkey FOREIGN KEY (userid) REFERENCES htp_user(userid)
);

CREATE INDEX refreshtoken_userid_idx ON refreshtoken(userid);
CREATE INDEX refreshtoken_familyid_idx ON refreshtoken(familyid);
CREATE INDEX refreshtoken_endvalid_idx ON refreshtoken(endvalid);
