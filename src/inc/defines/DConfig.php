<?php

namespace Hashtopolis\inc\defines;

use Hashtopolis\inc\DataSet;
use ReflectionClass;
use ReflectionException;

class DConfig {
  // Section: Cracking/Tasks
  const BENCHMARK_TIME         = "benchtime";
  const CHUNK_DURATION         = "chunktime";
  const CHUNK_TIMEOUT          = "chunktimeout";
  const AGENT_TIMEOUT          = "agenttimeout";
  const FIELD_SEPARATOR        = "fieldseparator";
  const HASHLIST_ALIAS         = "hashlistAlias";
  const STATUS_TIMER           = "statustimer";
  const BLACKLIST_CHARS        = "blacklistChars";
  const DISP_TOLERANCE         = "disptolerance";
  const DEFAULT_BENCH          = "defaultBenchmark";
  const RULE_SPLIT_SMALL_TASKS = "ruleSplitSmallTasks";
  const RULE_SPLIT_ALWAYS      = "ruleSplitAlways";
  const RULE_SPLIT_DISABLE     = "ruleSplitDisable";
  const AGENT_DATA_LIFETIME    = "agentDataLifetime";
  const DISABLE_TRIMMING       = "disableTrimming";
  const PRIORITY_0_START       = "priority0Start";
  const HASHCAT_BRAIN_ENABLE   = "hashcatBrainEnable";
  const HASHCAT_BRAIN_HOST     = "hashcatBrainHost";
  const HASHCAT_BRAIN_PORT     = "hashcatBrainPort";
  const HASHCAT_BRAIN_PASS     = "hashcatBrainPass";
  const HASHLIST_IMPORT_CHECK  = "hashlistImportCheck";
  const HC_ERROR_IGNORE        = "hcErrorIgnore";
  
  // Section: Yubikey
  const YUBIKEY_ID  = "yubikey_id";
  const YUBIKEY_KEY = "yubikey_key";
  const YUBIKEY_URL = "yubikey_url";
  
  // Section: Finetuning
  const HASHES_PAGE_SIZE           = "pagingSize";
  const NUMBER_LOGENTRIES          = "numLogEntries";
  const BATCH_SIZE                 = "batchSize";
  const PLAINTEXT_MAX_LENGTH       = "plainTextMaxLength";
  const HASH_MAX_LENGTH            = "hashMaxLength";
  const MAX_HASHLIST_SIZE          = "maxHashlistSize";
  const UAPI_SEND_TASK_IS_COMPLETE = "uApiSendTaskIsComplete";
  const DEFAULT_PAGE_SIZE          = "defaultPageSize";
  const MAX_PAGE_SIZE              = "maxPageSize";
  
  // Section: UI
  const TIME_FORMAT            = "timefmt";
  const DONATE_OFF             = "donateOff";
  const HIDE_IMPORT_MASKS      = "hideImportMasks";
  const HASHES_PER_PAGE        = "hashesPerPage";
  const HIDE_IP_INFO           = "hideIpInfo";
  const SHOW_TASK_PERFORMANCE  = "showTaskPerformance";
  const AGENT_STAT_LIMIT       = "agentStatLimit";
  const AGENT_STAT_TENSION     = "agentStatTension";
  const MAX_SESSION_LENGTH     = "maxSessionLength";
  const AGENT_TEMP_THRESHOLD_1 = "agentTempThreshold1";
  const AGENT_TEMP_THRESHOLD_2 = "agentTempThreshold2";
  const AGENT_UTIL_THRESHOLD_1 = "agentUtilThreshold1";
  const AGENT_UTIL_THRESHOLD_2 = "agentUtilThreshold2";
  
  // Section: Server
  const BASE_URL          = "baseUrl";
  const BASE_HOST         = "baseHost";
  const EMAIL_SENDER      = "emailSender";
  const EMAIL_SENDER_NAME = "emailSenderName";
  const CONTACT_EMAIL     = "contactEmail";
  const VOUCHER_DELETION  = "voucherDeletion";
  const SERVER_LOG_LEVEL  = "serverLogLevel";
  const ALLOW_DEREGISTER  = "allowDeregister";
  
  // Section: Multicast
  const MULTICAST_ENABLE    = "multicastEnable";
  const MULTICAST_DEVICE    = "multicastDevice";
  const MULTICAST_TR_ENABLE = "multicastTransferRateEnable";
  const MULTICAST_TR        = "multicastTranserRate";
  
  // Section: Notifications
  const NOTIFICATIONS_PROXY_ENABLE = "notificationsProxyEnable";
  const TELEGRAM_BOT_TOKEN         = "telegramBotToken";
  const NOTIFICATIONS_PROXY_SERVER = "notificationsProxyServer";
  const NOTIFICATIONS_PROXY_PORT   = "notificationsProxyPort";
  const NOTIFICATIONS_PROXY_TYPE   = "notificationsProxyType";
  
  static function getConstants() {
    try {
      $oClass = new ReflectionClass(__CLASS__);
    }
    catch (ReflectionException $e) {
      die("Exception: " . $e->getMessage());
    }
    return $oClass->getConstants();
  }
  
  /**
   * Gives the selection for the configuration values which are selections.
   * @param string $config
   * @return DataSet
   */
  public static function getSelection($config) {
    return match ($config) {
      DConfig::NOTIFICATIONS_PROXY_TYPE => new DataSet([
          DProxyTypes::HTTP => DProxyTypes::HTTP,
          DProxyTypes::HTTPS => DProxyTypes::HTTPS,
          DProxyTypes::SOCKS4 => DProxyTypes::SOCKS4,
          DProxyTypes::SOCKS5 => DProxyTypes::SOCKS5
        ]
      ),
      DConfig::SERVER_LOG_LEVEL => new DataSet([
          DServerLog::TRACE => "TRACE",
          DServerLog::DEBUG => "DEBUG",
          DServerLog::INFO => "INFO",
          DServerLog::WARNING => "WARNING",
          DServerLog::ERROR => "ERROR",
          DServerLog::FATAL => "FATAL"
        ]
      ),
      default => new DataSet(["Not found!"]),
    };
  }
  
  /**
   * Gives the format which a config input should have. Default is string if it's not a known config.
   * @param $config string
   * @return string
   */
  public static function getConfigType($config) {
    return match ($config) {
      DConfig::BENCHMARK_TIME => DConfigType::NUMBER_INPUT,
      DConfig::CHUNK_DURATION => DConfigType::NUMBER_INPUT,
      DConfig::CHUNK_TIMEOUT => DConfigType::NUMBER_INPUT,
      DConfig::AGENT_TIMEOUT => DConfigType::NUMBER_INPUT,
      DConfig::HASHES_PAGE_SIZE => DConfigType::NUMBER_INPUT,
      DConfig::FIELD_SEPARATOR => DConfigType::STRING_INPUT,
      DConfig::HASHLIST_ALIAS => DConfigType::STRING_INPUT,
      DConfig::STATUS_TIMER => DConfigType::NUMBER_INPUT,
      DConfig::BLACKLIST_CHARS => DConfigType::STRING_INPUT,
      DConfig::NUMBER_LOGENTRIES => DConfigType::NUMBER_INPUT,
      DConfig::TIME_FORMAT => DConfigType::STRING_INPUT,
      DConfig::BASE_URL => DConfigType::STRING_INPUT,
      Dconfig::DISP_TOLERANCE => DConfigType::NUMBER_INPUT,
      DConfig::BATCH_SIZE => DConfigType::NUMBER_INPUT,
      DConfig::BASE_HOST => DConfigType::STRING_INPUT,
      DConfig::DONATE_OFF => DConfigType::TICKBOX,
      DConfig::PLAINTEXT_MAX_LENGTH => DConfigType::NUMBER_INPUT,
      DConfig::HASH_MAX_LENGTH => DConfigType::NUMBER_INPUT,
      DConfig::EMAIL_SENDER => DConfigType::EMAIL,
      DConfig::MAX_HASHLIST_SIZE => DConfigType::NUMBER_INPUT,
      DConfig::HIDE_IMPORT_MASKS => DConfigType::TICKBOX,
      DConfig::TELEGRAM_BOT_TOKEN => DConfigType::STRING_INPUT,
      DConfig::CONTACT_EMAIL => DConfigType::EMAIL,
      DConfig::VOUCHER_DELETION => DConfigType::TICKBOX,
      DConfig::HASHES_PER_PAGE => DConfigType::NUMBER_INPUT,
      DConfig::HIDE_IP_INFO => DConfigType::TICKBOX,
      DConfig::EMAIL_SENDER_NAME => DConfigType::STRING_INPUT,
      DConfig::DEFAULT_BENCH => DConfigType::TICKBOX,
      DConfig::SHOW_TASK_PERFORMANCE => DConfigType::TICKBOX,
      DConfig::RULE_SPLIT_ALWAYS => DConfigType::TICKBOX,
      DConfig::RULE_SPLIT_SMALL_TASKS => DConfigType::TICKBOX,
      DConfig::RULE_SPLIT_DISABLE => DConfigType::TICKBOX,
      DConfig::AGENT_STAT_LIMIT => DConfigType::NUMBER_INPUT,
      DConfig::AGENT_DATA_LIFETIME => DConfigType::NUMBER_INPUT,
      DConfig::AGENT_STAT_TENSION => DConfigType::TICKBOX,
      DConfig::MULTICAST_ENABLE => DConfigType::TICKBOX,
      DConfig::MULTICAST_DEVICE => DConfigType::STRING_INPUT,
      DConfig::MULTICAST_TR_ENABLE => DConfigType::TICKBOX,
      DConfig::MULTICAST_TR => DConfigType::NUMBER_INPUT,
      DConfig::NOTIFICATIONS_PROXY_ENABLE => DConfigType::TICKBOX,
      DConfig::NOTIFICATIONS_PROXY_PORT => DConfigType::NUMBER_INPUT,
      DConfig::NOTIFICATIONS_PROXY_SERVER => DConfigType::STRING_INPUT,
      DConfig::NOTIFICATIONS_PROXY_TYPE => DConfigType::SELECT,
      DConfig::DISABLE_TRIMMING => DConfigType::TICKBOX,
      DConfig::PRIORITY_0_START => DConfigType::TICKBOX,
      DConfig::SERVER_LOG_LEVEL => DConfigType::SELECT,
      DConfig::MAX_SESSION_LENGTH => DConfigType::NUMBER_INPUT,
      DConfig::HASHCAT_BRAIN_ENABLE => DConfigType::TICKBOX,
      DConfig::HASHCAT_BRAIN_HOST => DConfigType::STRING_INPUT,
      DConfig::HASHCAT_BRAIN_PORT => DConfigType::NUMBER_INPUT,
      DConfig::HASHCAT_BRAIN_PASS => DConfigType::STRING_INPUT,
      DConfig::HASHLIST_IMPORT_CHECK => DConfigType::TICKBOX,
      DConfig::ALLOW_DEREGISTER => DConfigType::TICKBOX,
      DConfig::AGENT_TEMP_THRESHOLD_1 => DConfigType::NUMBER_INPUT,
      DConfig::AGENT_TEMP_THRESHOLD_2 => DConfigType::NUMBER_INPUT,
      DConfig::AGENT_UTIL_THRESHOLD_1 => DConfigType::NUMBER_INPUT,
      DConfig::AGENT_UTIL_THRESHOLD_2 => DConfigType::NUMBER_INPUT,
      DConfig::UAPI_SEND_TASK_IS_COMPLETE => DConfigType::TICKBOX,
      DConfig::HC_ERROR_IGNORE => DConfigType::STRING_INPUT,
      DConfig::DEFAULT_PAGE_SIZE => DConfigType::NUMBER_INPUT,
      DConfig::MAX_PAGE_SIZE => DConfigType::NUMBER_INPUT,
      default => DConfigType::STRING_INPUT,
    };
  }
  
  /**
   * @param $config string
   * @return string
   */
  public static function getConfigDescription($config) {
    return match ($config) {
      DConfig::BENCHMARK_TIME => "Time in seconds an agent should benchmark a task.",
      DConfig::CHUNK_DURATION => "Time in seconds a client should be working on a single chunk.",
      DConfig::CHUNK_TIMEOUT => "Time in seconds the server will consider an issued chunk as inactive or timed out and will reallocate to another client.",
      DConfig::AGENT_TIMEOUT => "Time in seconds the server will consider a client inactive or timed out.",
      DConfig::HASHES_PAGE_SIZE => "Number of hashes shown on each page of the hashes view.",
      DConfig::FIELD_SEPARATOR => "The separator character used to separate hash and plain (or salt).",
      DConfig::HASHLIST_ALIAS => "The string used as hashlist alias when creating a task.",
      DConfig::STATUS_TIMER => "Default interval in seconds clients should report back to the server for a task. (cracks, status, and progress).",
      DConfig::BLACKLIST_CHARS => "Characters that are not allowed to be used in attack command inputs.",
      DConfig::NUMBER_LOGENTRIES => "Number of log entries that should be saved. When this number is exceeded by 120%, the oldest will be overwritten.",
      DConfig::TIME_FORMAT => "Set the time format. Use syntax for PHPs date() method.",
      DConfig::BASE_URL => "Base url for the webpage (this does not include hostname and is normally determined automatically on the installation).",
      DConfig::DISP_TOLERANCE => "Allowable deviation in the final chunk of a task in percent.<br>(avoids issuing small chunks when the remaining part of a task is slightly bigger than the normal chunk size).",
      DConfig::BATCH_SIZE => "Batch size of SQL query when hashlist is sent to the agent.",
      DConfig::YUBIKEY_ID => "Yubikey Client ID.",
      DConfig::YUBIKEY_KEY => "Yubikey Secret Key.",
      DConfig::YUBIKEY_URL => "Yubikey API URL.",
      DConfig::BASE_HOST => "Base hostname/port/protocol to use. Only fill this in to override the auto-determined value.",
      DConfig::DONATE_OFF => "Hide donation information.",
      DConfig::PLAINTEXT_MAX_LENGTH => "Max length of a plaintext. (WARNING: This change may take a long time depending on DB size!)",
      DConfig::HASH_MAX_LENGTH => "Max length of a hash. (WARNING: This change may take a long time depending on DB size!)",
      DConfig::EMAIL_SENDER => "Email address used as sender on notification emails.",
      DConfig::MAX_HASHLIST_SIZE => "Max size of a hashlist in lines. (Prevents uploading very large lists).",
      DConfig::HIDE_IMPORT_MASKS => "Hide pre configured tasks that were imported through a mask import.",
      DConfig::TELEGRAM_BOT_TOKEN => "Telegram bot token used to send telegram notifications.",
      DConfig::CONTACT_EMAIL => "Admin email address that will be displayed on the webpage footer. (Leave empty to hide)",
      DConfig::VOUCHER_DELETION => "Vouchers can be used multiple times and will not be deleted automatically.",
      DConfig::HASHES_PER_PAGE => "Number of hashes per page on hashes view.",
      DConfig::HIDE_IP_INFO => "Hide agent's IP information.",
      DConfig::EMAIL_SENDER_NAME => "Sender's name on emails sent from " . APP_NAME . ".",
      DConfig::DEFAULT_BENCH => "Use speed benchmark as default.",
      DConfig::SHOW_TASK_PERFORMANCE => "Show cracks/minute for tasks which are running.",
      DConfig::RULE_SPLIT_SMALL_TASKS => "When rule splitting is applied for tasks, always make them a small task.",
      DConfig::RULE_SPLIT_ALWAYS => "Even do rule splitting when there are not enough rules but just the benchmark is too high.<br>Can result in subtasks with just one rule.",
      DConfig::RULE_SPLIT_DISABLE => "Disable automatic task splitting with large rule files.",
      DConfig::AGENT_STAT_LIMIT => "Maximal number of data points showing of agent gpu data.",
      DConfig::AGENT_DATA_LIFETIME => "Minimum time in seconds how long agent gpu/cpu utilisation and gpu temperature data is kept on the server.",
      DConfig::AGENT_STAT_TENSION => "Draw straigth lines in agent data graph  instead of bezier curves.",
      DConfig::MULTICAST_ENABLE => "Enable UDP multicast distribution of files to agents. (Make sure you did all the preparation before activating)<br>You can read more informations here: <a href='https://github.com/hashtopolis/runner/blob/master/README.md' target='_blank'>https://github.com/hashtopolis/runner</a>",
      DConfig::MULTICAST_DEVICE => "Network device of the server to be used for the multicast distribution.",
      DConfig::MULTICAST_TR_ENABLE => "Instead of the built in UFTP flow control, use a static set transfer rate<br>(Important: Setting this value wrong can affect the functionality, only use this if you are sure this transfer rate is feasible)",
      DConfig::MULTICAST_TR => "Set static transfer rate in case it is activated (in Kbit/s)",
      DConfig::NOTIFICATIONS_PROXY_ENABLE => "Enable using a proxy for sending notifications.",
      DConfig::NOTIFICATIONS_PROXY_PORT => "Set the port for the notifications proxy.",
      DConfig::NOTIFICATIONS_PROXY_SERVER => "Server url of the proxy to use for notifications.",
      DConfig::NOTIFICATIONS_PROXY_TYPE => "Proxy type to use for notifications.",
      DConfig::DISABLE_TRIMMING => "Disable trimming of chunks and redo whole chunks.",
      DConfig::PRIORITY_0_START => "Also automatically assign tasks with priority 0.",
      DConfig::SERVER_LOG_LEVEL => "Server level to be logged on the server to file.",
      DConfig::MAX_SESSION_LENGTH => "Max session length users can configure (in hours).",
      DConfig::HASHCAT_BRAIN_ENABLE => "Allow hashcat brain to be used for hashlists",
      DConfig::HASHCAT_BRAIN_HOST => "Host to be used for hashcat brain (must be reachable by agents)",
      DConfig::HASHCAT_BRAIN_PORT => "Port for hashcat brain",
      DConfig::HASHCAT_BRAIN_PASS => "Password to be used to access hashcat brain server",
      DConfig::HASHLIST_IMPORT_CHECK => "Check all hashes of a hashlist on import in case they are already cracked in another list",
      DConfig::ALLOW_DEREGISTER => "Allow clients to deregister themselves automatically from the server.",
      DConfig::AGENT_TEMP_THRESHOLD_1 => "Temperature threshold from which on an agent is shown in orange on the agent status page.",
      DConfig::AGENT_TEMP_THRESHOLD_2 => "Temperature threshold from which on an agent is shown in red on the agent status page.",
      DConfig::AGENT_UTIL_THRESHOLD_1 => "Util value where an agent is shown in orange on the agent status page, if below.",
      DConfig::AGENT_UTIL_THRESHOLD_2 => "Util value where an agent is shown in red on the agent status page, if below.",
      DConfig::UAPI_SEND_TASK_IS_COMPLETE => "Also send 'isComplete' for each task on the User API when listing all tasks (might affect performance)",
      DConfig::HC_ERROR_IGNORE => "Ignore error messages from crackers which contain given strings (multiple values separated by comma)",
      DConfig::DEFAULT_PAGE_SIZE => "The default page size of items that are returned in API calls.",
      DConfig::MAX_PAGE_SIZE => "The maximum page size of items that are allowed to return in an API call.",
      default => $config,
    };
  }
}