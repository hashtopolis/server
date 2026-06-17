-- the backtick as default blacklisted character got lost for postgres, so for the cases where people still have the default, we add it
UPDATE Config
SET value = '&|"''{}()[]$<>;`'
WHERE item = 'blacklistChars'
  AND configSectionId=1
  AND value = '&|"''{}()[]$<>;';