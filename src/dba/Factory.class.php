<?php

namespace DBA;

class Factory {
  private static $accessGroupFactory = null;
  private static $agentFactory = null;
  private static $agentBinaryFactory = null;
  private static $agentErrorFactory = null;
  private static $agentStatFactory = null;
  private static $agentZapFactory = null;
  private static $apiKeyFactory = null;
  private static $apiGroupFactory = null;
  private static $assignmentFactory = null;
  private static $chunkFactory = null;
  private static $configFactory = null;
  private static $configSectionFactory = null;
  private static $crackerBinaryFactory = null;
  private static $crackerBinaryTypeFactory = null;
  private static $fileFactory = null;
  private static $fileDeleteFactory = null;
  private static $fileDownloadFactory = null;
  private static $hashFactory = null;
  private static $hashBinaryFactory = null;
  private static $hashlistFactory = null;
  private static $hashTypeFactory = null;
  private static $healthCheckFactory = null;
  private static $healthCheckAgentFactory = null;
  private static $logEntryFactory = null;
  private static $notificationSettingFactory = null;
  private static $preprocessorFactory = null;
  private static $pretaskFactory = null;
  private static $regVoucherFactory = null;
  private static $rightGroupFactory = null;
  private static $sessionFactory = null;
  private static $speedFactory = null;
  private static $storedValueFactory = null;
  private static $supertaskFactory = null;
  private static $taskFactory = null;
  private static $taskDebugOutputFactory = null;
  private static $taskWrapperFactory = null;
  private static $userFactory = null;
  private static $zapFactory = null;
  private static $accessGroupUserFactory = null;
  private static $accessGroupAgentFactory = null;
  private static $fileTaskFactory = null;
  private static $filePretaskFactory = null;
  private static $supertaskPretaskFactory = null;
  private static $hashlistHashlistFactory = null;
  
  public static function getAccessGroupFactory() {
    if (self::$accessGroupFactory == null) {
      $f = new AccessGroupFactory();
      self::$accessGroupFactory = $f;
      return $f;
    } else {
      return self::$accessGroupFactory;
    }
  }
  
  public static function getAgentFactory() {
    if (self::$agentFactory == null) {
      $f = new AgentFactory();
      self::$agentFactory = $f;
      return $f;
    } else {
      return self::$agentFactory;
    }
  }
  
  public static function getAgentBinaryFactory() {
    if (self::$agentBinaryFactory == null) {
      $f = new AgentBinaryFactory();
      self::$agentBinaryFactory = $f;
      return $f;
    } else {
      return self::$agentBinaryFactory;
    }
  }
  
  public static function getAgentErrorFactory() {
    if (self::$agentErrorFactory == null) {
      $f = new AgentErrorFactory();
      self::$agentErrorFactory = $f;
      return $f;
    } else {
      return self::$agentErrorFactory;
    }
  }
  
  public static function getAgentStatFactory() {
    if (self::$agentStatFactory == null) {
      $f = new AgentStatFactory();
      self::$agentStatFactory = $f;
      return $f;
    } else {
      return self::$agentStatFactory;
    }
  }
  
  public static function getAgentZapFactory() {
    if (self::$agentZapFactory == null) {
      $f = new AgentZapFactory();
      self::$agentZapFactory = $f;
      return $f;
    } else {
      return self::$agentZapFactory;
    }
  }
  
  public static function getApiKeyFactory() {
    if (self::$apiKeyFactory == null) {
      $f = new ApiKeyFactory();
      self::$apiKeyFactory = $f;
      return $f;
    } else {
      return self::$apiKeyFactory;
    }
  }
  
  public static function getApiGroupFactory() {
    if (self::$apiGroupFactory == null) {
      $f = new ApiGroupFactory();
      self::$apiGroupFactory = $f;
      return $f;
    } else {
      return self::$apiGroupFactory;
    }
  }
  
  public static function getAssignmentFactory() {
    if (self::$assignmentFactory == null) {
      $f = new AssignmentFactory();
      self::$assignmentFactory = $f;
      return $f;
    } else {
      return self::$assignmentFactory;
    }
  }
  
  public static function getChunkFactory() {
    if (self::$chunkFactory == null) {
      $f = new ChunkFactory();
      self::$chunkFactory = $f;
      return $f;
    } else {
      return self::$chunkFactory;
    }
  }
  
  public static function getConfigFactory() {
    if (self::$configFactory == null) {
      $f = new ConfigFactory();
      self::$configFactory = $f;
      return $f;
    } else {
      return self::$configFactory;
    }
  }
  
  public static function getConfigSectionFactory() {
    if (self::$configSectionFactory == null) {
      $f = new ConfigSectionFactory();
      self::$configSectionFactory = $f;
      return $f;
    } else {
      return self::$configSectionFactory;
    }
  }
  
  public static function getCrackerBinaryFactory() {
    if (self::$crackerBinaryFactory == null) {
      $f = new CrackerBinaryFactory();
      self::$crackerBinaryFactory = $f;
      return $f;
    } else {
      return self::$crackerBinaryFactory;
    }
  }
  
  public static function getCrackerBinaryTypeFactory() {
    if (self::$crackerBinaryTypeFactory == null) {
      $f = new CrackerBinaryTypeFactory();
      self::$crackerBinaryTypeFactory = $f;
      return $f;
    } else {
      return self::$crackerBinaryTypeFactory;
    }
  }
  
  public static function getFileFactory() {
    if (self::$fileFactory == null) {
      $f = new FileFactory();
      self::$fileFactory = $f;
      return $f;
    } else {
      return self::$fileFactory;
    }
  }
  
  public static function getFileDeleteFactory() {
    if (self::$fileDeleteFactory == null) {
      $f = new FileDeleteFactory();
      self::$fileDeleteFactory = $f;
      return $f;
    } else {
      return self::$fileDeleteFactory;
    }
  }
  
  public static function getFileDownloadFactory() {
    if (self::$fileDownloadFactory == null) {
      $f = new FileDownloadFactory();
      self::$fileDownloadFactory = $f;
      return $f;
    } else {
      return self::$fileDownloadFactory;
    }
  }
  
  public static function getHashFactory() {
    if (self::$hashFactory == null) {
      $f = new HashFactory();
      self::$hashFactory = $f;
      return $f;
    } else {
      return self::$hashFactory;
    }
  }
  
  public static function getHashBinaryFactory() {
    if (self::$hashBinaryFactory == null) {
      $f = new HashBinaryFactory();
      self::$hashBinaryFactory = $f;
      return $f;
    } else {
      return self::$hashBinaryFactory;
    }
  }
  
  public static function getHashlistFactory() {
    if (self::$hashlistFactory == null) {
      $f = new HashlistFactory();
      self::$hashlistFactory = $f;
      return $f;
    } else {
      return self::$hashlistFactory;
    }
  }
  
  public static function getHashTypeFactory() {
    if (self::$hashTypeFactory == null) {
      $f = new HashTypeFactory();
      self::$hashTypeFactory = $f;
      return $f;
    } else {
      return self::$hashTypeFactory;
    }
  }
  
  public static function getHealthCheckFactory() {
    if (self::$healthCheckFactory == null) {
      $f = new HealthCheckFactory();
      self::$healthCheckFactory = $f;
      return $f;
    } else {
      return self::$healthCheckFactory;
    }
  }
  
  public static function getHealthCheckAgentFactory() {
    if (self::$healthCheckAgentFactory == null) {
      $f = new HealthCheckAgentFactory();
      self::$healthCheckAgentFactory = $f;
      return $f;
    } else {
      return self::$healthCheckAgentFactory;
    }
  }
  
  public static function getLogEntryFactory() {
    if (self::$logEntryFactory == null) {
      $f = new LogEntryFactory();
      self::$logEntryFactory = $f;
      return $f;
    } else {
      return self::$logEntryFactory;
    }
  }
  
  public static function getNotificationSettingFactory() {
    if (self::$notificationSettingFactory == null) {
      $f = new NotificationSettingFactory();
      self::$notificationSettingFactory = $f;
      return $f;
    } else {
      return self::$notificationSettingFactory;
    }
  }
  
  public static function getPreprocessorFactory() {
    if (self::$preprocessorFactory == null) {
      $f = new PreprocessorFactory();
      self::$preprocessorFactory = $f;
      return $f;
    } else {
      return self::$preprocessorFactory;
    }
  }
  
  public static function getPretaskFactory() {
    if (self::$pretaskFactory == null) {
      $f = new PretaskFactory();
      self::$pretaskFactory = $f;
      return $f;
    } else {
      return self::$pretaskFactory;
    }
  }
  
  public static function getRegVoucherFactory() {
    if (self::$regVoucherFactory == null) {
      $f = new RegVoucherFactory();
      self::$regVoucherFactory = $f;
      return $f;
    } else {
      return self::$regVoucherFactory;
    }
  }
  
  public static function getRightGroupFactory() {
    if (self::$rightGroupFactory == null) {
      $f = new RightGroupFactory();
      self::$rightGroupFactory = $f;
      return $f;
    } else {
      return self::$rightGroupFactory;
    }
  }
  
  public static function getSessionFactory() {
    if (self::$sessionFactory == null) {
      $f = new SessionFactory();
      self::$sessionFactory = $f;
      return $f;
    } else {
      return self::$sessionFactory;
    }
  }
  
  public static function getSpeedFactory() {
    if (self::$speedFactory == null) {
      $f = new SpeedFactory();
      self::$speedFactory = $f;
      return $f;
    } else {
      return self::$speedFactory;
    }
  }
  
  public static function getStoredValueFactory() {
    if (self::$storedValueFactory == null) {
      $f = new StoredValueFactory();
      self::$storedValueFactory = $f;
      return $f;
    } else {
      return self::$storedValueFactory;
    }
  }
  
  public static function getSupertaskFactory() {
    if (self::$supertaskFactory == null) {
      $f = new SupertaskFactory();
      self::$supertaskFactory = $f;
      return $f;
    } else {
      return self::$supertaskFactory;
    }
  }
  
  public static function getTaskFactory() {
    if (self::$taskFactory == null) {
      $f = new TaskFactory();
      self::$taskFactory = $f;
      return $f;
    } else {
      return self::$taskFactory;
    }
  }
  
  public static function getTaskDebugOutputFactory() {
    if (self::$taskDebugOutputFactory == null) {
      $f = new TaskDebugOutputFactory();
      self::$taskDebugOutputFactory = $f;
      return $f;
    } else {
      return self::$taskDebugOutputFactory;
    }
  }
  
  public static function getTaskWrapperFactory() {
    if (self::$taskWrapperFactory == null) {
      $f = new TaskWrapperFactory();
      self::$taskWrapperFactory = $f;
      return $f;
    } else {
      return self::$taskWrapperFactory;
    }
  }
  
  public static function getUserFactory() {
    if (self::$userFactory == null) {
      $f = new UserFactory();
      self::$userFactory = $f;
      return $f;
    } else {
      return self::$userFactory;
    }
  }
  
  public static function getZapFactory() {
    if (self::$zapFactory == null) {
      $f = new ZapFactory();
      self::$zapFactory = $f;
      return $f;
    } else {
      return self::$zapFactory;
    }
  }
  
  public static function getAccessGroupUserFactory() {
    if (self::$accessGroupUserFactory == null) {
      $f = new AccessGroupUserFactory();
      self::$accessGroupUserFactory = $f;
      return $f;
    } else {
      return self::$accessGroupUserFactory;
    }
  }
  
  public static function getAccessGroupAgentFactory() {
    if (self::$accessGroupAgentFactory == null) {
      $f = new AccessGroupAgentFactory();
      self::$accessGroupAgentFactory = $f;
      return $f;
    } else {
      return self::$accessGroupAgentFactory;
    }
  }
  
  public static function getFileTaskFactory() {
    if (self::$fileTaskFactory == null) {
      $f = new FileTaskFactory();
      self::$fileTaskFactory = $f;
      return $f;
    } else {
      return self::$fileTaskFactory;
    }
  }
  
  public static function getFilePretaskFactory() {
    if (self::$filePretaskFactory == null) {
      $f = new FilePretaskFactory();
      self::$filePretaskFactory = $f;
      return $f;
    } else {
      return self::$filePretaskFactory;
    }
  }
  
  public static function getSupertaskPretaskFactory() {
    if (self::$supertaskPretaskFactory == null) {
      $f = new SupertaskPretaskFactory();
      self::$supertaskPretaskFactory = $f;
      return $f;
    } else {
      return self::$supertaskPretaskFactory;
    }
  }
  
  public static function getHashlistHashlistFactory() {
    if (self::$hashlistHashlistFactory == null) {
      $f = new HashlistHashlistFactory();
      self::$hashlistHashlistFactory = $f;
      return $f;
    } else {
      return self::$hashlistHashlistFactory;
    }
  }
  
  const FILTER = "filter";
  const JOIN = "join";
  const ORDER = "order";
  const UPDATE = "update";
  const GROUP = "group";
}
