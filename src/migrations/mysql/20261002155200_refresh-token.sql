-- Refresh tokens: long-lived, single-use credentials that are exchanged for short-lived access tokens.
-- Only the SHA-256 hash of the token string is stored. Tokens rotate on every use; all rotations of one
-- login session share a familyId so a replayed token can revoke the entire session.
CREATE TABLE `RefreshToken` (
  `refreshTokenId` int NOT NULL AUTO_INCREMENT,
  `userId` int NOT NULL,
  `tokenHash` varchar(64) NOT NULL,
  `familyId` varchar(32) NOT NULL,
  `issuedAt` bigint NOT NULL,
  `endValid` bigint NOT NULL,
  `usedAt` bigint DEFAULT NULL,
  `isRevoked` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`refreshTokenId`),
  UNIQUE KEY `uq_refreshToken_tokenHash` (`tokenHash`),
  KEY `idx_refreshToken_userId` (`userId`),
  KEY `idx_refreshToken_familyId` (`familyId`),
  KEY `idx_refreshToken_endValid` (`endValid`),
  CONSTRAINT `fk_refreshToken_user` FOREIGN KEY (`userId`) REFERENCES `htp_User` (`userId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
