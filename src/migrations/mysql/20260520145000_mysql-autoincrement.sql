-- Make that HashtType also uses AUTO_INCREMENT to be consistent with the SERIAL we have in postgres.
-- But it is set to higher than you would ever have in hc modes to avoid collisions.
ALTER TABLE `HashType`
    MODIFY `hashTypeId` INT NOT NULL AUTO_INCREMENT,
    AUTO_INCREMENT = 100000;