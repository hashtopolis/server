-- Realign the chunk states with hashcat's status_rc_t enum. The server used to store the
-- pre-SELFTEST hashcat numbering while agents reported hashcat's status codes shifted down
-- by one, so 2..10 move up by one. 12, 13 and 15 are the shifted forms of hashcat's ERROR
-- (13), ABORTED_FINISH (14) and AUTODETECT (16) which agents wrote when running newer
-- hashcat versions. 0 (INIT) and 1 (AUTOTUNE) keep their meaning. A single CASE expression
-- per row avoids any ordering hazards.
UPDATE chunk SET state = CASE state
  WHEN 2 THEN 3
  WHEN 3 THEN 4
  WHEN 4 THEN 5
  WHEN 5 THEN 6
  WHEN 6 THEN 7
  WHEN 7 THEN 8
  WHEN 8 THEN 9
  WHEN 9 THEN 10
  WHEN 10 THEN 11
  WHEN 12 THEN 13
  WHEN 13 THEN 14
  WHEN 15 THEN 16
  ELSE state
END
WHERE state BETWEEN 2 AND 15;
