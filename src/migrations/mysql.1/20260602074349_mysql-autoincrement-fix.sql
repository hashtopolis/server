-- The previous migration could affect the HashType 0 to be changed due to mysql silently on autoincrement application.
UPDATE HashType
SET hashTypeId = 0
WHERE hashTypeId = 100000
  AND description = 'MD5'
  AND NOT EXISTS (
    SELECT 1
    FROM (
             SELECT 1
             FROM HashType
             WHERE hashTypeId = 0
         ) AS temp_check
);