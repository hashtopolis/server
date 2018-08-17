<?php

use DBA\User;
use DBA\Task;
use DBA\Chunk;
use DBA\JoinFilter;
use DBA\QueryFilter;
use DBA\Hash;
use DBA\Hashlist;
use DBA\Config;
use DBA\ConfigSection;

class ConfigUtils {
  /**
   * @param Config $config
   * @param boolean $new
   */
  public static function set($config, $new) {
    global $FACTORIES;

    if($config->getItem() == DConfig::MULTICAST_ENABLE && $config->getValue()){
      // multicast was ticked to enable -> start runner
      RunnerUtils::startService();
    }
    else if($config->getItem() == DConfig::MULTICAST_ENABLE && !$config->getValue()){
      // multicast was ticked to disable -> stop runner
      RunnerUtils::stopService();
    }

    if ($new) {
      $FACTORIES::getConfigFactory()->save($config);
    }
    else {
      $FACTORIES::getConfigFactory()->update($config);
    }
  }

  /**
   * @param string $item
   * @throws HTException
   * @return Config
   */
  public static function get($item) {
    global $FACTORIES;

    $qF = new QueryFilter(Config::ITEM, $item, "=");
    $config = $FACTORIES::getConfigFactory()->filter(array($FACTORIES::FILTER => $qF), true);
    if ($config == null) {
      throw new HTException("Item not found!");
    }
    return $config;
  }

  /**
   * @return ConfigSection[]
   */
  public static function getSections() {
    global $FACTORIES;

    return $FACTORIES::getConfigSectionFactory()->filter([]);
  }

  /**
   * @return Config[]
   */
  public static function getAll() {
    global $FACTORIES;

    return $FACTORIES::getConfigFactory()->filter([]);
  }

  /**
   * @param array $arr
   * @throws HTException
   */
  public static function updateConfig($arr) {
    global $OBJECTS, $FACTORIES;

    foreach ($arr as $item => $val) {
      if (substr($item, 0, 7) == "config_") {
        $name = substr($item, 7);
        if (SConfig::getInstance()->getVal($name) == $val) {
          continue; // the value was not changed, so we don't need to update it
        }

        $qF = new QueryFilter(Config::ITEM, $name, "=");
        $config = $FACTORIES::getConfigFactory()->filter(array($FACTORIES::FILTER => array($qF)), true);
        if ($config == null) {
          $config = new Config(0, 5, $name, $val);
          $FACTORIES::getConfigFactory()->save($config);
        }
        else {
          if ($name == DConfig::HASH_MAX_LENGTH) {
            $limit = intval($val);
            if (!Util::setMaxHashLength($limit)) {
              throw new HTException("Failed to update max hash length!");
            }
          }
          else if ($name == DConfig::PLAINTEXT_MAX_LENGTH) {
            $limit = intval($val);
            if (!Util::setPlaintextMaxLength($limit)) {
              throw new HTException("Failed to update max plaintext length!");
            }
          }
          SConfig::getInstance()->addValue($name, $val);
          $config->setValue($val);
          ConfigUtils::set($config, false);
        }
      }
    }
    SConfig::reload();
    $OBJECTS['config'] = SConfig::getInstance();
  }

  /**
   * @return int[]
   */
  public static function rebuildCache() {
    global $FACTORIES;

    $correctedChunks = 0;
    $correctedHashlists = 0;

    //check chunks
    $FACTORIES::getAgentFactory()->getDB()->beginTransaction();
    $taskWrappers = $FACTORIES::getTaskWrapperFactory()->filter(array());
    foreach ($taskWrappers as $taskWrapper) {
      $hashlists = Util::checkSuperHashlist($FACTORIES::getHashlistFactory()->get($taskWrapper->getHashlistId()));

      $jF = new JoinFilter($FACTORIES::getTaskFactory(), Task::TASK_ID, Chunk::TASK_ID, $FACTORIES::getChunkFactory());
      $qF = new QueryFilter(Task::TASK_WRAPPER_ID, $taskWrapper->getId(), "=", $FACTORIES::getTaskFactory());
      $joined = $FACTORIES::getChunkFactory()->filter(array($FACTORIES::JOIN => $jF, $FACTORIES::FILTER => $qF));
      /** @var $chunks Chunk[] */
      $chunks = $joined[$FACTORIES::getChunkFactory()->getModelName()];

      foreach ($chunks as $chunk) {
        $hashFactory = ($hashlists[0]->getFormat() == DHashlistFormat::PLAIN) ? $FACTORIES::getHashFactory() : $FACTORIES::getHashBinaryFactory();
        $qF1 = new QueryFilter(Hash::CHUNK_ID, $chunk->getId(), "=");
        $qF2 = new QueryFilter(Hash::IS_CRACKED, "1", "=");
        $count = $hashFactory->countFilter(array($FACTORIES::FILTER => array($qF1, $qF2)));
        if ($count != $chunk->getCracked()) {
          $correctedChunks++;
          $chunk->setCracked($count);
          $FACTORIES::getChunkFactory()->update($chunk);
        }
      }
    }
    $FACTORIES::getAgentFactory()->getDB()->commit();

    //check hashlists
    $FACTORIES::getAgentFactory()->getDB()->beginTransaction();
    $qF = new QueryFilter(Hashlist::FORMAT, DHashlistFormat::SUPERHASHLIST, "<>");
    $hashlists = $FACTORIES::getHashlistFactory()->filter(array($FACTORIES::FILTER => $qF));
    foreach ($hashlists as $hashlist) {
      $qF1 = new QueryFilter(Hash::HASHLIST_ID, $hashlist->getId(), "=");
      $qF2 = new QueryFilter(Hash::IS_CRACKED, "1", "=");
      $hashFactory = $FACTORIES::getHashFactory();
      if ($hashlist->getFormat() != DHashlistFormat::PLAIN) {
        $hashFactory = $FACTORIES::getHashBinaryFactory();
      }
      $count = $hashFactory->countFilter(array($FACTORIES::FILTER => array($qF1, $qF2)));
      if ($count != $hashlist->getCracked()) {
        $correctedHashlists++;
        $hashlist->setCracked($count);
        $FACTORIES::getHashlistFactory()->update($hashlist);
      }
    }
    $FACTORIES::getAgentFactory()->getDB()->commit();

    //check superhashlists
    $FACTORIES::getAgentFactory()->getDB()->beginTransaction();
    $qF = new QueryFilter(Hashlist::FORMAT, DHashlistFormat::SUPERHASHLIST, "=");
    $superHashlists = $FACTORIES::getHashlistFactory()->filter(array($FACTORIES::FILTER => $qF));
    foreach ($superHashlists as $superHashlist) {
      $hashlists = Util::checkSuperHashlist($superHashlist);
      $cracked = 0;
      foreach ($hashlists as $hashlist) {
        $cracked += $hashlist->getCracked();
      }
      if ($cracked != $superHashlist->getCracked()) {
        $correctedHashlists++;
        $superHashlist->setCracked($cracked);
        $FACTORIES::getHashlistFactory()->update($superHashlist);
      }
    }
    $FACTORIES::getAgentFactory()->getDB()->commit();

    return [$correctedChunks, $correctedHashlists];
  }

  /**
   * @throws HTMessages
   */
  public static function scanFiles() {
    global $FACTORIES;

    $allOk = true;
    $messages = [];
    $files = $FACTORIES::getFileFactory()->filter(array());
    foreach ($files as $file) {
      $absolutePath = dirname(__FILE__) . "/../../files/" . $file->getFilename();
      if (!file_exists($absolutePath)) {
        $messages[] = "File " . $file->getFilename() . " does not exist!";
        $allOk = false;
        continue;
      }
      $size = Util::filesize($absolutePath);
      if ($size == -1) {
        $allOk = false;
        $messages[] = "Failed to determine file size of " . $file->getFilename();
      }
      else if ($size != $file->getSize()) {
        $allOk = false;
        $messages[] = "File size mismatch of " . $file->getFilename() . ", will be corrected.";
        $file->setSize($size);
        $FACTORIES::getFileFactory()->update($file);
      }
    }
    if (!$allOk) {
      throw new HTMessages($messages);
    }
  }

  /**
   * @param User $user
   */
  public static function clearAll($user) {
    global $FACTORIES;

    $FACTORIES::getAgentFactory()->getDB()->beginTransaction();
    $FACTORIES::getHashFactory()->massDeletion(array());
    $FACTORIES::getHashBinaryFactory()->massDeletion(array());
    $FACTORIES::getAssignmentFactory()->massDeletion(array());
    $FACTORIES::getAgentErrorFactory()->massDeletion(array());
    $FACTORIES::getChunkFactory()->massDeletion(array());
    $FACTORIES::getZapFactory()->massDeletion(array());
    $FACTORIES::getFileTaskFactory()->massDeletion(array());
    $FACTORIES::getTaskFactory()->massDeletion(array());
    $FACTORIES::getTaskWrapperFactory()->massDeletion(array());
    $FACTORIES::getHashlistHashlistFactory()->massDeletion(array());
    $FACTORIES::getHashlistFactory()->massDeletion(array());
    $FACTORIES::getAgentFactory()->getDB()->commit();
    Util::createLogEntry("User", $user->getId(), DLogEntry::WARN, "Complete clear was executed!");
  }
}