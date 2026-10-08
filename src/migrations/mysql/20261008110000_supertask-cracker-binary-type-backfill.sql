-- The supertask cracker binary type column was added with hashcat (1) as the
-- default for existing rows. Derive the real type from the pretasks: a
-- supertask takes the type of its first pretask (the lowest pretaskId).
-- Supertasks without pretasks keep the default, there is nothing to derive
-- from. Supertasks mixing cracker types keep the type of their first pretask,
-- their other pretasks can no longer run and the supertask has to be split
-- by an admin. Idempotent: rerunning sets the same values.
UPDATE Supertask s
JOIN (
  SELECT sp.supertaskId, p.crackerBinaryTypeId
  FROM SupertaskPretask sp
  JOIN Pretask p ON p.pretaskId = sp.pretaskId
  WHERE sp.pretaskId = (
    SELECT MIN(sp2.pretaskId) FROM SupertaskPretask sp2 WHERE sp2.supertaskId = sp.supertaskId
  )
) firstPretask ON firstPretask.supertaskId = s.supertaskId
SET s.crackerBinaryTypeId = firstPretask.crackerBinaryTypeId;
