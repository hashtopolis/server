<?php /** @noinspection SqlNoDataSourceInspection */
use DBA\Factory;

if (!Util::databaseTableExists("HardwareGroup")) {
  Factory::getAgentFactory()->getDB()->query("CREATE TABLE `HardwareGroup` (
      `hardwareGroupId`    INT(11)      NOT NULL,
      `devices`            VARCHAR(65000) NOT NULL
  ) ENGINE=InnoDB;"
  );
  Factory::getAgentFactory()->getDB()->query("ALTER TABLE `HardwareGroup` MODIFY `hardwareGroupId` int(11) NOT NULL AUTO_INCREMENT;");
  Factory::getAgentFactory()->getDB()->query("ALTER TABLE `HardwareGroup` ADD PRIMARY KEY (`hardwareGroupId`);");
}

//change Agent table
Factory::getAgentFactory()->getDB()->query("ALTER TABLE `Agent`
  DROP COLUMN devices;
");
Factory::getAgentFactory()->getDB()->query("ALTER TABLE `Agent`
  ADD COLUMN hardwareGroupId INT(11);
");
Factory::getAgentFactory()->getDB()->query("ALTER TABLE `Agent` ADD CONSTRAINT FOREIGN KEY (`hardwareGroupId`) REFERENCES `HardwareGroup` (`hardwareGroupId`);");


if (!Util::databaseTableExists("Benchmark")) {
  Factory::getAgentFactory()->getDB()->query("CREATE TABLE `Benchmark` (
      `benchmarkId`         INT(11)      NOT NULL,
      `benchmarkValue`      VARCHAR(256) NOT NULL,
      `hardwareGroupId`      INT(11) NOT NULL,
      `attackParameters`     VARCHAR(512) NOT NULL,
      `ttl`                  INT(11) NULL,
      `hashMode`             INT(11) NULL,
      `benchmarkType`       VARCHAR(10) NULL
    ) ENGINE=InnoDB;"
  );

  Factory::getAgentFactory()->getDB()->query("ALTER TABLE `Benchmark` MODIFY `benchmarkId` int(11) NOT NULL AUTO_INCREMENT;");

  Factory::getAgentFactory()->getDB()->query("
    ALTER TABLE `Benchmark`
    ADD PRIMARY KEY (`benchmarkId`),
    ADD KEY `hardwareGroupId` (`hardwareGroupId`);");

  Factory::getAgentFactory()->getDB()->query("ALTER TABLE `Benchmark`
  ADD CONSTRAINT `Benchmark_ibfk_1` FOREIGN KEY (`hardwareGroupId`) REFERENCES `HardwareGroup` (`hardwareGroupId`);"); 
}

Factory::getAgentFactory()->getDB()->query("ALTER TABLE `Agent` ADD CONSTRAINT `Agent_ibfk_2` 
      FOREIGN KEY (`hardwareGroupId`) REFERENCES `HardwareGroup` (`hardwareGroupId`);");
Factory::getAgentFactory()->getDB()->query("ALTER TABLE `Benchmark` ADD CONSTRAINT `Benchmark_ibfk_1` 
      FOREIGN KEY (`hardwareGroupId`) REFERENCES `HardwareGroup` (`hardwareGroupId`);");

?>