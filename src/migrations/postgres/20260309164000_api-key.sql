CREATE TABLE JwtApiKey (
    JwtApiKeyId SERIAL NOT NULL PRIMARY KEY,
    userId INTEGER REFERENCES htp_User(id),
    startValid bigint,
    endValid bigint,
    isRevoked BOOLEAN DEFAULT FALSE,
    CONSTRAINT fk_JwtApiKey_user
        FOREIGN KEY (userId) REFERENCES htp_User(id),
    CONSTRAINT idx_JwtApiKey_userId
        INDEX (userId)
);