<?php /** @noinspection SqlNoDataSourceInspection */
use DBA\Factory;

if (!isset($PRESENT["v0.14.x_benchmark_cache"])) {
  if (!Util::databaseTableExists("HardwareGroup")) {
    Factory::getAgentFactory()->getDB()->query("CREATE TABLE `HardwareGroup` (
        `hardwareGroupId`    INT(11)      NOT NULL,
        `devices`            VARCHAR(65000) NOT NULL
    ) ENGINE=InnoDB;"
    );
    
    Factory::getAgentFactory()->getDB()->query("ALTER TABLE `HardwareGroup`
      ADD PRIMARY KEY (`hardwareGroupId`);
    ");

    Factory::getAgentFactory()->getDB()->query("ALTER TABLE `HardwareGroup` MODIFY `hardwareGroupId` int(11) NOT NULL AUTO_INCREMENT;");

    //change Agent table
    Factory::getAgentFactory()->getDB()->query("ALTER TABLE `Agent`
      DROP COLUMN devices;
    ");

    Factory::getAgentFactory()->getDB()->query("ALTER TABLE `Agent`
      ADD COLUMN hardwareGroupId INT(11) DEFAULT NULL; 
    ");
    Factory::getAgentFactory()->getDB()->query("ALTER TABLE `Agent` ADD CONSTRAINT FOREIGN KEY (`hardwareGroupId`) REFERENCES `HardwareGroup` (`hardwareGroupId`);");

    Factory::getAgentFactory()->getDB()->query("CREATE TABLE `Benchmark` (
        `benchmarkId`         INT(11)      NOT NULL,
        `benchmarkValue`      VARCHAR(256) NOT NULL,
        `hardwareGroupId`      INT(11) NOT NULL,
        `crackerBinaryId`      INT(11) NOT NULL,
        `attackParameters`     VARCHAR(512) NOT NULL,
        `ttl`                  INT(11) NULL,
        `hashMode`             INT(11) NULL,
        `benchmarkType`       VARCHAR(10) NULL
      ) ENGINE=InnoDB;"
    );

    Factory::getAgentFactory()->getDB()->query("ALTER TABLE `Benchmark`
      ADD PRIMARY KEY (`benchmarkId`),
      ADD KEY `crackerBinaryId` (`crackerBinaryId`),
      ADD KEY `hardwareGroupId` (`hardwareGroupId`);");

    Factory::getAgentFactory()->getDB()->query("ALTER TABLE `Benchmark` MODIFY `benchmarkId` int(11) NOT NULL AUTO_INCREMENT;");

    Factory::getAgentFactory()->getDB()->query("ALTER TABLE `Benchmark`
    ADD CONSTRAINT `Benchmark_ibfk_1` FOREIGN KEY (`hardwareGroupId`) REFERENCES `HardwareGroup` (`hardwareGroupId`);"); 

    Factory::getAgentFactory()->getDB()->query("INSERT INTO `Config` (`configId`, `configSectionId`, `item`, `value`) VALUES
    (78, 1, 'benchmarkcacheTtl', '216000');"); 

    Factory::getAgentFactory()->getDB()->query("INSERT INTO `HardwareGroup` (`hardwareGroupId`, `devices`) VALUES
    (0, 'Default');"); 

    $EXECUTED["v0.14.x_benchmark_cache"] = true;
  }
}