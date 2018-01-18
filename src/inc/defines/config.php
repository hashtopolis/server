<?php

class DConfigType {
  const STRING_INPUT = "string";
  const NUMBER_INPUT = "number";
  const TICKBOX      = "checkbox";
  const EMAIL        = "email";
}

class DConfigAction {
  const UPDATE_CONFIG = "updateConfig";
  const REBUILD_CACHE = "rebuildCache";
  const RESCAN_FILES  = "rescanFiles";
  const CLEAR_ALL     = "clearAll";
}

// used config values
class DConfig {
  // Section: Cracking/Tasks
  const BENCHMARK_TIME  = "benchtime";
  const CHUNK_DURATION  = "chunktime";
  const CHUNK_TIMEOUT   = "chunktimeout";
  const AGENT_TIMEOUT   = "agenttimeout";
  const FIELD_SEPARATOR = "fieldseparator";
  const HASHLIST_ALIAS  = "hashlistAlias";
  const STATUS_TIMER    = "statustimer";
  const BLACKLIST_CHARS = "blacklistChars";
  const DISP_TOLERANCE  = "disptolerance";
  
  // Section: Yubikey
  const YUBIKEY_ID  = "yubikey_id";
  const YUBIKEY_KEY = "yubikey_key";
  const YUBIKEY_URL = "yubikey_url";
  
  // Section: Finetuning
  const HASHES_PAGE_SIZE             = "pagingSize";
  const NUMBER_LOGENTRIES            = "numLogEntries";
  const BATCH_SIZE                   = "batchSize";
  const HASHLIST_DOWNLOAD_CHUNK_SIZE = "hashlistDownloadChunkSize";
  const PLAINTEXT_MAX_LENGTH         = "plainTextMaxLength";
  const HASH_MAX_LENGTH              = "hashMaxLength";
  const MAX_HASHLIST_SIZE            = "maxHashlistSize";
  
  // Section: UI
  const TIME_FORMAT       = "timefmt";
  const DONATE_OFF        = "donateOff";
  const HIDE_IMPORT_MASKS = "hideImportMasks";
  
  // Section: Server
  const BASE_URL           = "baseUrl";
  const BASE_HOST          = "baseHost";
  const EMAIL_SENDER       = "emailSender";
  const TELEGRAM_BOT_TOKEN = "telegramBotToken";
  const CONTACT_EMAIL      = "contactEmail";
  
  
  /**
   * Gives the format which a config input should have. Default is string if it's not a known config.
   * @param $config string
   * @return string
   */
  public static function getConfigType($config) {
    switch ($config) {
      case DConfig::BENCHMARK_TIME:
        return DConfigType::NUMBER_INPUT;
      case DConfig::CHUNK_DURATION:
        return DConfigType::NUMBER_INPUT;
      case DConfig::CHUNK_TIMEOUT:
        return DConfigType::NUMBER_INPUT;
      case DConfig::AGENT_TIMEOUT:
        return DConfigType::NUMBER_INPUT;
      case DConfig::HASHES_PAGE_SIZE:
        return DConfigType::NUMBER_INPUT;
      case DConfig::FIELD_SEPARATOR:
        return DConfigType::STRING_INPUT;
      case DConfig::HASHLIST_ALIAS:
        return DConfigType::STRING_INPUT;
      case DConfig::STATUS_TIMER:
        return DConfigType::NUMBER_INPUT;
      case DConfig::BLACKLIST_CHARS:
        return DConfigType::STRING_INPUT;
      case DConfig::NUMBER_LOGENTRIES:
        return DConfigType::NUMBER_INPUT;
      case DConfig::TIME_FORMAT:
        return DConfigType::STRING_INPUT;
      case DConfig::BASE_URL:
        return DConfigType::STRING_INPUT;
      case Dconfig::DISP_TOLERANCE:
        return DConfigType::NUMBER_INPUT;
      case DConfig::BATCH_SIZE:
        return DConfigType::NUMBER_INPUT;
      case DConfig::BASE_HOST:
        return DConfigType::STRING_INPUT;
      case DConfig::DONATE_OFF:
        return DConfigType::NUMBER_INPUT;
      case DConfig::HASHLIST_DOWNLOAD_CHUNK_SIZE:
        return DConfigType::NUMBER_INPUT;
      case DConfig::PLAINTEXT_MAX_LENGTH:
        return DConfigType::NUMBER_INPUT;
      case DConfig::HASH_MAX_LENGTH:
        return DConfigType::NUMBER_INPUT;
      case DConfig::EMAIL_SENDER:
        return DConfigType::EMAIL;
      case DConfig::MAX_HASHLIST_SIZE:
        return DConfigType::NUMBER_INPUT;
      case DConfig::HIDE_IMPORT_MASKS:
        return DConfigType::NUMBER_INPUT;
      case DConfig::TELEGRAM_BOT_TOKEN:
        return DConfigType::STRING_INPUT;
      case DConfig::CONTACT_EMAIL:
        return DConfigType::EMAIL;
    }
    return DConfigType::STRING_INPUT;
  }
  
  /**
   * @param $config string
   * @return string
   */
  public static function getConfigDescription($config) {
    switch ($config) {
      case DConfig::BENCHMARK_TIME:
        return "Time in seconds an agent should benchmark a task";
      case DConfig::CHUNK_DURATION:
        return "How long an agent approximately should need for completing one chunk";
      case DConfig::CHUNK_TIMEOUT:
        return "How long an agent must not respond until it is treated as timed out and the chunk will get shipped to other agents";
      case DConfig::AGENT_TIMEOUT:
        return "How long an agent must not respond until he is treated as not active anymore";
      case DConfig::HASHES_PAGE_SIZE:
        return "How many hashes are showed at once in the hashes view (Page size)";
      case DConfig::FIELD_SEPARATOR:
        return "What separator should be used to separate hash and plain (or salt)";
      case DConfig::HASHLIST_ALIAS:
        return "What string is used as hashlist alias when creating a task";
      case DConfig::STATUS_TIMER:
        return "After how many seconds the agent should send its progress and cracks to the server";
      case DConfig::BLACKLIST_CHARS:
        return "Chars which are not allowed to be used in attack command inputs";
      case DConfig::NUMBER_LOGENTRIES:
        return "How many log entries should be saved. When this number is exceeded by 120%, the oldest ones will get deleted";
      case DConfig::TIME_FORMAT:
        return "Set the formatting of time displaying. Use syntax for PHPs date() method";
      case DConfig::BASE_URL:
        return "Base url for the webpage (this does not include hostname and is normally determined automatically on the installation)";
      case DConfig::DISP_TOLERANCE:
        return "How many percent a chunk can be longer than normal to finish a task (this avoids small chunks if the remaining part is slightly bigger than the normal chunk)";
      case DConfig::BATCH_SIZE:
        return "Batch size of SQL query when hashlist is sent to the agent";
      case DConfig::YUBIKEY_ID:
        return "Yubikey Client Id";
      case DConfig::YUBIKEY_KEY:
        return "Yubikey Secret Key";
      case DConfig::YUBIKEY_URL:
        return "Yubikey API Url";
      case DConfig::BASE_HOST:
        return "Base hostname/port/protocol to use. Only fill in to override the self-determined value.";
      case DConfig::DONATE_OFF:
        return "Hide donate information (insert '1' to hide)";
      case DConfig::HASHLIST_DOWNLOAD_CHUNK_SIZE:
        return "Size of blocks in hashes which will be sent to the client when downloading a hashlist.";
      case DConfig::PLAINTEXT_MAX_LENGTH:
        return "Max length of a plaintext. (WARNING: changing this might take longer depending on your DB size!)";
      case DConfig::HASH_MAX_LENGTH:
        return "Max length of a hash. (WARNING: changing this might take longer depending on your DB size!)";
      case DConfig::EMAIL_SENDER:
        return "Email which is used as sender on notification emails.";
      case DConfig::MAX_HASHLIST_SIZE:
        return "Max size of a hashlist (this is to prevent people blocking the server with uploading very large stuff).";
      case DConfig::HIDE_IMPORT_MASKS:
        return "Hide pretasks which were imported through a mask lines import.";
      case DConfig::TELEGRAM_BOT_TOKEN:
        return "Telegram bot token to use to send telegram notifications.";
      case DConfig::CONTACT_EMAIL:
        return "Email address which will be displayed on the footer as admin contact. (Leave empty to hide)";
    }
    return $config;
  }
}