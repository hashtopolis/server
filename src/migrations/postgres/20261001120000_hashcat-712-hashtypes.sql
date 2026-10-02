-- hashcat 7.1.2 introduced two new hash-modes which are not part of the seed
-- list of the initial migration. Fresh installs get them from hashtypes.json
-- during the setup, this migration provides them for upgraded installs, so
-- the state does not depend on the scan of the binary completing. The scan
-- creates missing hashtypes as well, the NOT EXISTS keeps this idempotent for
-- installs where the scan already added them.
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash)
SELECT 24901, 'Besder Authentication MD5', 0, 0
WHERE NOT EXISTS (SELECT 1 FROM hashtype WHERE hashtypeid = 24901);

INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash)
SELECT 74000, 'Generic Hash [Bridged: Rust]', 0, 1
WHERE NOT EXISTS (SELECT 1 FROM hashtype WHERE hashtypeid = 74000);
