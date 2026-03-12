CREATE TABLE JwtApiKey (
    jwtApiKeyId INT NOT NULL AUTO_INCREMENT,
    userId INTEGER,
    startValid bigint NOT NULL,
    endValid bigint NOT NULL,
    isRevoked BOOLEAN NOT NULL DEFAULT FALSE,
    PRIMARY KEY (`jwtApiKeyId`),
    KEY `idx_JwtApiKey_userId` (`userId`),
    CONSTRAINT `fk_JwtApiKey_user`
        FOREIGN KEY (`userId`) REFERENCES `htp_User`(`userId`)
);