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
  const DEFAULT_BENCH   = "defaultBenchmark";
  
  // Section: Yubikey
  const YUBIKEY_ID  = "yubikey_id";
  const YUBIKEY_KEY = "yubikey_key";
  const YUBIKEY_URL = "yubikey_url";
  
  // Section: Finetuning
  const HASHES_PAGE_SIZE     = "pagingSize";
  const NUMBER_LOGENTRIES    = "numLogEntries";
  const BATCH_SIZE           = "batchSize";
  const PLAINTEXT_MAX_LENGTH = "plainTextMaxLength";
  const HASH_MAX_LENGTH      = "hashMaxLength";
  const MAX_HASHLIST_SIZE    = "maxHashlistSize";
  
  // Section: UI
  const TIME_FORMAT       = "timefmt";
  const DONATE_OFF        = "donateOff";
  const HIDE_IMPORT_MASKS = "hideImportMasks";
  const HASHES_PER_PAGE   = "hashesPerPage";
  const HIDE_IP_INFO      = "hideIpInfo";
  
  // Section: Server
  const BASE_URL           = "baseUrl";
  const BASE_HOST          = "baseHost";
  const EMAIL_SENDER       = "emailSender";
  const EMAIL_SENDER_NAME  = "emailSenderName";
  const TELEGRAM_BOT_TOKEN = "telegramBotToken";
  const CONTACT_EMAIL      = "contactEmail";
  const VOUCHER_DELETION   = "voucherDeletion";
  
  
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
        return DConfigType::TICKBOX;
      case DConfig::PLAINTEXT_MAX_LENGTH:
        return DConfigType::NUMBER_INPUT;
      case DConfig::HASH_MAX_LENGTH:
        return DConfigType::NUMBER_INPUT;
      case DConfig::EMAIL_SENDER:
        return DConfigType::EMAIL;
      case DConfig::MAX_HASHLIST_SIZE:
        return DConfigType::NUMBER_INPUT;
      case DConfig::HIDE_IMPORT_MASKS:
        return DConfigType::TICKBOX;
      case DConfig::TELEGRAM_BOT_TOKEN:
        return DConfigType::STRING_INPUT;
      case DConfig::CONTACT_EMAIL:
        return DConfigType::EMAIL;
      case DConfig::VOUCHER_DELETION:
        return DConfigType::TICKBOX;
      case DConfig::HASHES_PER_PAGE:
        return DConfigType::NUMBER_INPUT;
      case DConfig::HIDE_IP_INFO:
        return DConfigType::TICKBOX;
      case DConfig::EMAIL_SENDER_NAME:
        return DConfigType::STRING_INPUT;
      case DConfig::DEFAULT_BENCH:
        return DConfigType::TICKBOX;
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
        return "Time in seconds a client should be working on a single chunk";
      case DConfig::CHUNK_TIMEOUT:
        return "Time in seconds the server will consider an issued chunk as inactive or timed out and will reallocate to another client.";
      case DConfig::AGENT_TIMEOUT:
        return "Time in seconds the server will consider a client inactive or timed out.";
      case DConfig::HASHES_PAGE_SIZE:
        return "Number of hashes shown on each page of the hashes view.";
      case DConfig::FIELD_SEPARATOR:
        return "The separator character used to separate hash and plain (or salt)";
      case DConfig::HASHLIST_ALIAS:
        return "The string used as hashlist alias when creating a task";
      case DConfig::STATUS_TIMER:
        return "Interval in seconds clients should report back to the server. (cracks, status, and progress)";
      case DConfig::BLACKLIST_CHARS:
        return "Characters that are not allowed to be used in attack command inputs";
      case DConfig::NUMBER_LOGENTRIES:
        return "Number of log entries that should be saved. When this number is exceeded by 120%, the oldest will be overwritten";
      case DConfig::TIME_FORMAT:
        return "Set the time format. Use syntax for PHPs date() method";
      case DConfig::BASE_URL:
        return "Base url for the webpage (this does not include hostname and is normally determined automatically on the installation)";
      case DConfig::DISP_TOLERANCE:
        return "Allowable deviation in the final chunk of a task in percent. (avoids issuing small chunks when the remaining part of a task is slightly bigger than the normal chunk size)";
      case DConfig::BATCH_SIZE:
        return "Batch size of SQL query when hashlist is sent to the agent";
      case DConfig::YUBIKEY_ID:
        return "Yubikey Client Id";
      case DConfig::YUBIKEY_KEY:
        return "Yubikey Secret Key";
      case DConfig::YUBIKEY_URL:
        return "Yubikey API Url";
      case DConfig::BASE_HOST:
        return "Base hostname/port/protocol to use. Only fill this in to override the auto-determined value.";
      case DConfig::DONATE_OFF:
        return "Hide donation information";
      case DConfig::PLAINTEXT_MAX_LENGTH:
        return "Max length of a plaintext. (WARNING: This change may take a long time depending on DB size!)";
      case DConfig::HASH_MAX_LENGTH:
        return "Max length of a hash. (WARNING: This change may take a long time depending on DB size!)";
      case DConfig::EMAIL_SENDER:
        return "Email address used as sender on notification emails.";
      case DConfig::MAX_HASHLIST_SIZE:
        return "Max size of a hashlist in lines. (Prevents uploading very large lists).";
      case DConfig::HIDE_IMPORT_MASKS:
        return "Hide pre configured tasks that were imported through a mask import.";
      case DConfig::TELEGRAM_BOT_TOKEN:
        return "Telegram bot token used to send telegram notifications.";
      case DConfig::CONTACT_EMAIL:
        return "Admin email address that will be displayed on the webpage footer. (Leave empty to hide)";
      case DConfig::VOUCHER_DELETION:
        return "Vouchers can be used multiple times and will not be deleted automatically.";
      case DConfig::HASHES_PER_PAGE:
        return "Number of hashes per page on hashes view.";
      case DConfig::HIDE_IP_INFO:
        return "Hide agent's IP information.";
      case DConfig::EMAIL_SENDER_NAME:
        return "Sender's name on emails sent from Hashtopussy.";
      case DConfig::DEFAULT_BENCH:
        return "Use speed benchmark as default.";
    }
    return $config;
  }
}
