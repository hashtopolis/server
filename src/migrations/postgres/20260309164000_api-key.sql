CREATE TABLE JwtApiKey (
    JwtApiKeyId SERIAL NOT NULL PRIMARY KEY,
    userId INTEGER,
    startValid bigint,
    endValid bigint,
    isRevoked BOOLEAN DEFAULT FALSE,
    CONSTRAINT fk_JwtApiKey_user
        FOREIGN KEY (userId) REFERENCES htp_User(id)
);
CREATE INDEX idx_JwtApiKey_userId ON JwtApiKey (userId);