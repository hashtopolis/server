CREATE TABLE JwtApiKey (
    jwtApiKeyId SERIAL NOT NULL PRIMARY KEY,
    userId INTEGER,
    startValid bigint NOT NULL,
    endValid bigint NOT NULL,
    isRevoked BOOLEAN NOT NULL DEFAULT FALSE,
    CONSTRAINT fk_JwtApiKey_user
        FOREIGN KEY (userId) REFERENCES htp_User(userId)
);
CREATE INDEX idx_jwtApiKey_userId ON JwtApiKey (userId);