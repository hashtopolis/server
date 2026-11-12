UPDATE AgentBinary SET version='0.8.0' WHERE version='0.7.7' AND filename='hashtopolis.zip' AND binaryType='python';
UPDATE AgentBinary SET version='0.2.0' WHERE (version='0.1.3' OR version='0.1.4') AND filename='rust-agent.zip' AND binaryType='rust';
