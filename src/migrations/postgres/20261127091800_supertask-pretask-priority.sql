-- A pretask reused in several supertasks shared a single priority, so changing
-- it from one supertask reordered all the others. The priority a pretask has
-- inside a supertask is stored on the association instead, taken from the
-- pretask when the association is created. Existing associations take the
-- priority of their pretask.
ALTER TABLE supertaskpretask ADD COLUMN priority INT NOT NULL DEFAULT 0;

UPDATE supertaskpretask sp
SET priority = p.priority
FROM pretask p
WHERE p.pretaskid = sp.pretaskid;

-- the priority must be provided explicitly, no silent default for new rows
ALTER TABLE ONLY supertaskpretask ALTER COLUMN priority DROP DEFAULT;
