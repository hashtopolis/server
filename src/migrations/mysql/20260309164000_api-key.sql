CREATE TABLE JwtApiKey (
    JwtApiKeyId INT NOT NULL,
    userId INTEGER REFERENCES htp_User(id),
    startValid bigint,
    endValid bigint,
    isRevoked BOOLEAN DEFAULT FALSE,
    PRIMARY KEY (`JwtApiKeyId`),
    KEY `idx_JwtApiKey_userId` (`userId`),
    CONSTRAINT `fk_JwtApiKey_user`
        FOREIGN KEY (`userId`) REFERENCES `htp_User`(`userId`)
);