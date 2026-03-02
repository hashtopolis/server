<?php

namespace Hashtopolis\dba;

use Hashtopolis\dba\models\AccessGroupFactory;
use Hashtopolis\dba\models\AgentFactory;
use Hashtopolis\dba\models\AgentBinaryFactory;
use Hashtopolis\dba\models\AgentErrorFactory;
use Hashtopolis\dba\models\AgentStatFactory;
use Hashtopolis\dba\models\AgentZapFactory;
use Hashtopolis\dba\models\ApiKeyFactory;
use Hashtopolis\dba\models\ApiGroupFactory;
use Hashtopolis\dba\models\AssignmentFactory;
use Hashtopolis\dba\models\ChunkFactory;
use Hashtopolis\dba\models\ConfigFactory;
use Hashtopolis\dba\models\ConfigSectionFactory;
use Hashtopolis\dba\models\CrackerBinaryFactory;
use Hashtopolis\dba\models\CrackerBinaryTypeFactory;
use Hashtopolis\dba\models\FileFactory;
use Hashtopolis\dba\models\FileDeleteFactory;
use Hashtopolis\dba\models\FileDownloadFactory;
use Hashtopolis\dba\models\HashFactory;
use Hashtopolis\dba\models\HashBinaryFactory;
use Hashtopolis\dba\models\HashlistFactory;
use Hashtopolis\dba\models\HashTypeFactory;
use Hashtopolis\dba\models\HealthCheckFactory;
use Hashtopolis\dba\models\HealthCheckAgentFactory;
use Hashtopolis\dba\models\LogEntryFactory;
use Hashtopolis\dba\models\NotificationSettingFactory;
use Hashtopolis\dba\models\PreprocessorFactory;
use Hashtopolis\dba\models\PretaskFactory;
use Hashtopolis\dba\models\RegVoucherFactory;
use Hashtopolis\dba\models\RightGroupFactory;
use Hashtopolis\dba\models\SessionFactory;
use Hashtopolis\dba\models\SpeedFactory;
use Hashtopolis\dba\models\StoredValueFactory;
use Hashtopolis\dba\models\SupertaskFactory;
use Hashtopolis\dba\models\TaskFactory;
use Hashtopolis\dba\models\TaskDebugOutputFactory;
use Hashtopolis\dba\models\TaskWrapperFactory;
use Hashtopolis\dba\models\UserFactory;
use Hashtopolis\dba\models\ZapFactory;
use Hashtopolis\dba\models\AccessGroupUserFactory;
use Hashtopolis\dba\models\AccessGroupAgentFactory;
use Hashtopolis\dba\models\FileTaskFactory;
use Hashtopolis\dba\models\FilePretaskFactory;
use Hashtopolis\dba\models\SupertaskPretaskFactory;
use Hashtopolis\dba\models\HashlistHashlistFactory;

class Factory {
  private static ?AccessGroupFactory $accessGroupFactory = null;
  private static ?AgentFactory $agentFactory = null;
  private static ?AgentBinaryFactory $agentBinaryFactory = null;
  private static ?AgentErrorFactory $agentErrorFactory = null;
  private static ?AgentStatFactory $agentStatFactory = null;
  private static ?AgentZapFactory $agentZapFactory = null;
  private static ?ApiKeyFactory $apiKeyFactory = null;
  private static ?ApiGroupFactory $apiGroupFactory = null;
  private static ?AssignmentFactory $assignmentFactory = null;
  private static ?ChunkFactory $chunkFactory = null;
  private static ?ConfigFactory $configFactory = null;
  private static ?ConfigSectionFactory $configSectionFactory = null;
  private static ?CrackerBinaryFactory $crackerBinaryFactory = null;
  private static ?CrackerBinaryTypeFactory $crackerBinaryTypeFactory = null;
  private static ?FileFactory $fileFactory = null;
  private static ?FileDeleteFactory $fileDeleteFactory = null;
  private static ?FileDownloadFactory $fileDownloadFactory = null;
  private static ?HashFactory $hashFactory = null;
  private static ?HashBinaryFactory $hashBinaryFactory = null;
  private static ?HashlistFactory $hashlistFactory = null;
  private static ?HashTypeFactory $hashTypeFactory = null;
  private static ?HealthCheckFactory $healthCheckFactory = null;
  private static ?HealthCheckAgentFactory $healthCheckAgentFactory = null;
  private static ?LogEntryFactory $logEntryFactory = null;
  private static ?NotificationSettingFactory $notificationSettingFactory = null;
  private static ?PreprocessorFactory $preprocessorFactory = null;
  private static ?PretaskFactory $pretaskFactory = null;
  private static ?RegVoucherFactory $regVoucherFactory = null;
  private static ?RightGroupFactory $rightGroupFactory = null;
  private static ?SessionFactory $sessionFactory = null;
  private static ?SpeedFactory $speedFactory = null;
  private static ?StoredValueFactory $storedValueFactory = null;
  private static ?SupertaskFactory $supertaskFactory = null;
  private static ?TaskFactory $taskFactory = null;
  private static ?TaskDebugOutputFactory $taskDebugOutputFactory = null;
  private static ?TaskWrapperFactory $taskWrapperFactory = null;
  private static ?UserFactory $userFactory = null;
  private static ?ZapFactory $zapFactory = null;
  private static ?AccessGroupUserFactory $accessGroupUserFactory = null;
  private static ?AccessGroupAgentFactory $accessGroupAgentFactory = null;
  private static ?FileTaskFactory $fileTaskFactory = null;
  private static ?FilePretaskFactory $filePretaskFactory = null;
  private static ?SupertaskPretaskFactory $supertaskPretaskFactory = null;
  private static ?HashlistHashlistFactory $hashlistHashlistFactory = null;
  
  public static function getAccessGroupFactory(): AccessGroupFactory {
    if (self::$accessGroupFactory == null) {
      $f = new AccessGroupFactory();
      self::$accessGroupFactory = $f;
      return $f;
    } else {
      return self::$accessGroupFactory;
    }
  }
  
  public static function getAgentFactory(): AgentFactory {
    if (self::$agentFactory == null) {
      $f = new AgentFactory();
      self::$agentFactory = $f;
      return $f;
    } else {
      return self::$agentFactory;
    }
  }
  
  public static function getAgentBinaryFactory(): AgentBinaryFactory {
    if (self::$agentBinaryFactory == null) {
      $f = new AgentBinaryFactory();
      self::$agentBinaryFactory = $f;
      return $f;
    } else {
      return self::$agentBinaryFactory;
    }
  }
  
  public static function getAgentErrorFactory(): AgentErrorFactory {
    if (self::$agentErrorFactory == null) {
      $f = new AgentErrorFactory();
      self::$agentErrorFactory = $f;
      return $f;
    } else {
      return self::$agentErrorFactory;
    }
  }
  
  public static function getAgentStatFactory(): AgentStatFactory {
    if (self::$agentStatFactory == null) {
      $f = new AgentStatFactory();
      self::$agentStatFactory = $f;
      return $f;
    } else {
      return self::$agentStatFactory;
    }
  }
  
  public static function getAgentZapFactory(): AgentZapFactory {
    if (self::$agentZapFactory == null) {
      $f = new AgentZapFactory();
      self::$agentZapFactory = $f;
      return $f;
    } else {
      return self::$agentZapFactory;
    }
  }
  
  public static function getApiKeyFactory(): ApiKeyFactory {
    if (self::$apiKeyFactory == null) {
      $f = new ApiKeyFactory();
      self::$apiKeyFactory = $f;
      return $f;
    } else {
      return self::$apiKeyFactory;
    }
  }
  
  public static function getApiGroupFactory(): ApiGroupFactory {
    if (self::$apiGroupFactory == null) {
      $f = new ApiGroupFactory();
      self::$apiGroupFactory = $f;
      return $f;
    } else {
      return self::$apiGroupFactory;
    }
  }
  
  public static function getAssignmentFactory(): AssignmentFactory {
    if (self::$assignmentFactory == null) {
      $f = new AssignmentFactory();
      self::$assignmentFactory = $f;
      return $f;
    } else {
      return self::$assignmentFactory;
    }
  }
  
  public static function getChunkFactory(): ChunkFactory {
    if (self::$chunkFactory == null) {
      $f = new ChunkFactory();
      self::$chunkFactory = $f;
      return $f;
    } else {
      return self::$chunkFactory;
    }
  }
  
  public static function getConfigFactory(): ConfigFactory {
    if (self::$configFactory == null) {
      $f = new ConfigFactory();
      self::$configFactory = $f;
      return $f;
    } else {
      return self::$configFactory;
    }
  }
  
  public static function getConfigSectionFactory(): ConfigSectionFactory {
    if (self::$configSectionFactory == null) {
      $f = new ConfigSectionFactory();
      self::$configSectionFactory = $f;
      return $f;
    } else {
      return self::$configSectionFactory;
    }
  }
  
  public static function getCrackerBinaryFactory(): CrackerBinaryFactory {
    if (self::$crackerBinaryFactory == null) {
      $f = new CrackerBinaryFactory();
      self::$crackerBinaryFactory = $f;
      return $f;
    } else {
      return self::$crackerBinaryFactory;
    }
  }
  
  public static function getCrackerBinaryTypeFactory(): CrackerBinaryTypeFactory {
    if (self::$crackerBinaryTypeFactory == null) {
      $f = new CrackerBinaryTypeFactory();
      self::$crackerBinaryTypeFactory = $f;
      return $f;
    } else {
      return self::$crackerBinaryTypeFactory;
    }
  }
  
  public static function getFileFactory(): FileFactory {
    if (self::$fileFactory == null) {
      $f = new FileFactory();
      self::$fileFactory = $f;
      return $f;
    } else {
      return self::$fileFactory;
    }
  }
  
  public static function getFileDeleteFactory(): FileDeleteFactory {
    if (self::$fileDeleteFactory == null) {
      $f = new FileDeleteFactory();
      self::$fileDeleteFactory = $f;
      return $f;
    } else {
      return self::$fileDeleteFactory;
    }
  }
  
  public static function getFileDownloadFactory(): FileDownloadFactory {
    if (self::$fileDownloadFactory == null) {
      $f = new FileDownloadFactory();
      self::$fileDownloadFactory = $f;
      return $f;
    } else {
      return self::$fileDownloadFactory;
    }
  }
  
  public static function getHashFactory(): HashFactory {
    if (self::$hashFactory == null) {
      $f = new HashFactory();
      self::$hashFactory = $f;
      return $f;
    } else {
      return self::$hashFactory;
    }
  }
  
  public static function getHashBinaryFactory(): HashBinaryFactory {
    if (self::$hashBinaryFactory == null) {
      $f = new HashBinaryFactory();
      self::$hashBinaryFactory = $f;
      return $f;
    } else {
      return self::$hashBinaryFactory;
    }
  }
  
  public static function getHashlistFactory(): HashlistFactory {
    if (self::$hashlistFactory == null) {
      $f = new HashlistFactory();
      self::$hashlistFactory = $f;
      return $f;
    } else {
      return self::$hashlistFactory;
    }
  }
  
  public static function getHashTypeFactory(): HashTypeFactory {
    if (self::$hashTypeFactory == null) {
      $f = new HashTypeFactory();
      self::$hashTypeFactory = $f;
      return $f;
    } else {
      return self::$hashTypeFactory;
    }
  }
  
  public static function getHealthCheckFactory(): HealthCheckFactory {
    if (self::$healthCheckFactory == null) {
      $f = new HealthCheckFactory();
      self::$healthCheckFactory = $f;
      return $f;
    } else {
      return self::$healthCheckFactory;
    }
  }
  
  public static function getHealthCheckAgentFactory(): HealthCheckAgentFactory {
    if (self::$healthCheckAgentFactory == null) {
      $f = new HealthCheckAgentFactory();
      self::$healthCheckAgentFactory = $f;
      return $f;
    } else {
      return self::$healthCheckAgentFactory;
    }
  }
  
  public static function getLogEntryFactory(): LogEntryFactory {
    if (self::$logEntryFactory == null) {
      $f = new LogEntryFactory();
      self::$logEntryFactory = $f;
      return $f;
    } else {
      return self::$logEntryFactory;
    }
  }
  
  public static function getNotificationSettingFactory(): NotificationSettingFactory {
    if (self::$notificationSettingFactory == null) {
      $f = new NotificationSettingFactory();
      self::$notificationSettingFactory = $f;
      return $f;
    } else {
      return self::$notificationSettingFactory;
    }
  }
  
  public static function getPreprocessorFactory(): PreprocessorFactory {
    if (self::$preprocessorFactory == null) {
      $f = new PreprocessorFactory();
      self::$preprocessorFactory = $f;
      return $f;
    } else {
      return self::$preprocessorFactory;
    }
  }
  
  public static function getPretaskFactory(): PretaskFactory {
    if (self::$pretaskFactory == null) {
      $f = new PretaskFactory();
      self::$pretaskFactory = $f;
      return $f;
    } else {
      return self::$pretaskFactory;
    }
  }
  
  public static function getRegVoucherFactory(): RegVoucherFactory {
    if (self::$regVoucherFactory == null) {
      $f = new RegVoucherFactory();
      self::$regVoucherFactory = $f;
      return $f;
    } else {
      return self::$regVoucherFactory;
    }
  }
  
  public static function getRightGroupFactory(): RightGroupFactory {
    if (self::$rightGroupFactory == null) {
      $f = new RightGroupFactory();
      self::$rightGroupFactory = $f;
      return $f;
    } else {
      return self::$rightGroupFactory;
    }
  }
  
  public static function getSessionFactory(): SessionFactory {
    if (self::$sessionFactory == null) {
      $f = new SessionFactory();
      self::$sessionFactory = $f;
      return $f;
    } else {
      return self::$sessionFactory;
    }
  }
  
  public static function getSpeedFactory(): SpeedFactory {
    if (self::$speedFactory == null) {
      $f = new SpeedFactory();
      self::$speedFactory = $f;
      return $f;
    } else {
      return self::$speedFactory;
    }
  }
  
  public static function getStoredValueFactory(): StoredValueFactory {
    if (self::$storedValueFactory == null) {
      $f = new StoredValueFactory();
      self::$storedValueFactory = $f;
      return $f;
    } else {
      return self::$storedValueFactory;
    }
  }
  
  public static function getSupertaskFactory(): SupertaskFactory {
    if (self::$supertaskFactory == null) {
      $f = new SupertaskFactory();
      self::$supertaskFactory = $f;
      return $f;
    } else {
      return self::$supertaskFactory;
    }
  }
  
  public static function getTaskFactory(): TaskFactory {
    if (self::$taskFactory == null) {
      $f = new TaskFactory();
      self::$taskFactory = $f;
      return $f;
    } else {
      return self::$taskFactory;
    }
  }
  
  public static function getTaskDebugOutputFactory(): TaskDebugOutputFactory {
    if (self::$taskDebugOutputFactory == null) {
      $f = new TaskDebugOutputFactory();
      self::$taskDebugOutputFactory = $f;
      return $f;
    } else {
      return self::$taskDebugOutputFactory;
    }
  }
  
  public static function getTaskWrapperFactory(): TaskWrapperFactory {
    if (self::$taskWrapperFactory == null) {
      $f = new TaskWrapperFactory();
      self::$taskWrapperFactory = $f;
      return $f;
    } else {
      return self::$taskWrapperFactory;
    }
  }
  
  public static function getUserFactory(): UserFactory {
    if (self::$userFactory == null) {
      $f = new UserFactory();
      self::$userFactory = $f;
      return $f;
    } else {
      return self::$userFactory;
    }
  }
  
  public static function getZapFactory(): ZapFactory {
    if (self::$zapFactory == null) {
      $f = new ZapFactory();
      self::$zapFactory = $f;
      return $f;
    } else {
      return self::$zapFactory;
    }
  }
  
  public static function getAccessGroupUserFactory(): AccessGroupUserFactory {
    if (self::$accessGroupUserFactory == null) {
      $f = new AccessGroupUserFactory();
      self::$accessGroupUserFactory = $f;
      return $f;
    } else {
      return self::$accessGroupUserFactory;
    }
  }
  
  public static function getAccessGroupAgentFactory(): AccessGroupAgentFactory {
    if (self::$accessGroupAgentFactory == null) {
      $f = new AccessGroupAgentFactory();
      self::$accessGroupAgentFactory = $f;
      return $f;
    } else {
      return self::$accessGroupAgentFactory;
    }
  }
  
  public static function getFileTaskFactory(): FileTaskFactory {
    if (self::$fileTaskFactory == null) {
      $f = new FileTaskFactory();
      self::$fileTaskFactory = $f;
      return $f;
    } else {
      return self::$fileTaskFactory;
    }
  }
  
  public static function getFilePretaskFactory(): FilePretaskFactory {
    if (self::$filePretaskFactory == null) {
      $f = new FilePretaskFactory();
      self::$filePretaskFactory = $f;
      return $f;
    } else {
      return self::$filePretaskFactory;
    }
  }
  
  public static function getSupertaskPretaskFactory(): SupertaskPretaskFactory {
    if (self::$supertaskPretaskFactory == null) {
      $f = new SupertaskPretaskFactory();
      self::$supertaskPretaskFactory = $f;
      return $f;
    } else {
      return self::$supertaskPretaskFactory;
    }
  }
  
  public static function getHashlistHashlistFactory(): HashlistHashlistFactory {
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
  const LIMIT = "limit";
}
