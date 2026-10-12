-- Add the new rust agent binary (initial release 0.1.1) for existing installs.
-- Idempotent: skipped if a binary of this type was already created manually.
INSERT INTO AgentBinary (binaryType, version, operatingSystems, filename, updateTrack, updateAvailable)
SELECT 'rust', '0.1.1', 'Windows, Linux, OS X', 'rust-agent.zip', 'stable', ''
WHERE NOT EXISTS (SELECT 1 FROM AgentBinary WHERE binaryType = 'rust');
