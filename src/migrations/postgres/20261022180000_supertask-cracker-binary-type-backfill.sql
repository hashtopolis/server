-- The supertask cracker binary type column was added with hashcat (1) as the
-- default for existing rows. Derive the real type from the pretasks: a
-- supertask takes the type of its first pretask (the lowest pretaskid).
-- Supertasks without pretasks keep the default, there is nothing to derive
-- from. Supertasks mixing cracker types keep the type of their first pretask,
-- their other pretasks can no longer run and the supertask has to be split
-- by an admin. Idempotent: rerunning sets the same values.
UPDATE supertask s
SET crackerbinarytypeid = p.crackerbinarytypeid
FROM supertaskpretask sp
JOIN pretask p ON p.pretaskid = sp.pretaskid
WHERE sp.supertaskid = s.supertaskid
  AND sp.pretaskid = (
    SELECT MIN(sp2.pretaskid) FROM supertaskpretask sp2 WHERE sp2.supertaskid = s.supertaskid
  );
