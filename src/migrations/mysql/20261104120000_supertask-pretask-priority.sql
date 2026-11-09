-- A pretask reused in several supertasks shared a single priority, so changing
-- it from one supertask reordered all the others. The priority a pretask has
-- inside a supertask is stored on the association instead, taken from the
-- pretask when the association is created. Existing associations take the
-- priority of their pretask.
ALTER TABLE SupertaskPretask ADD COLUMN priority int NOT NULL DEFAULT 0 AFTER pretaskId;

UPDATE SupertaskPretask sp
JOIN Pretask p ON p.pretaskId = sp.pretaskId
SET sp.priority = p.priority;

-- the priority must be provided explicitly, no silent default for new rows
ALTER TABLE SupertaskPretask ALTER priority DROP DEFAULT;
