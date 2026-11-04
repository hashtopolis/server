UPDATE AgentBinary SET version='0.7.7' WHERE version='0.7.6' AND filename='hashtopolis.zip' AND binaryType='python';
UPDATE AgentBinary SET version='0.1.3' WHERE (version='0.1.1' OR version='0.1.2') AND filename='rust-agent.zip' AND binaryType='rust';
