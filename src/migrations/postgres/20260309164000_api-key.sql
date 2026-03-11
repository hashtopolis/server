CREATE TABLE JwtApiKey (
    JwtApiKeyId SERIAL PRIMARY KEY,
    userId INTEGER REFERENCES htp_User(id),
    startValid bigint,
    endValid bigint,
    isRevoked BOOLEAN DEFAULT FALSE);