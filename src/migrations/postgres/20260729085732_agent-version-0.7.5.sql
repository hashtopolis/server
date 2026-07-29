-- In case of an upgrade from 0.14.8 where with new setups the column was still named 'type', rename it to 'binaryType' before the UPDATE.
-- Even though with postgres this never should've happened as with version 0.14.8 there was no postgres support.
DO $$
BEGIN
  IF EXISTS (SELECT 1 FROM information_schema.columns WHERE table_schema = current_schema() AND table_name = 'AgentBinary' AND column_name = 'type') THEN
    ALTER TABLE "AgentBinary" RENAME COLUMN "type" TO "binaryType";
  END IF;
END $$;

UPDATE AgentBinary SET version='0.7.5' WHERE version='0.7.4' AND filename='hashtopolis.zip' AND binaryType='python';
