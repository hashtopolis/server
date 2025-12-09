DROP INDEX IF EXISTS hash_hash_idx;
CREATE UNIQUE INDEX IF NOT EXISTS hash_hash_idx ON hash(hashtext(hash));
