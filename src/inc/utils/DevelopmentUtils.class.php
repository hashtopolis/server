<?php

class DevelopmentUtils {
  /**
   * @param $identifier
   * @throws HTException
   */
  public static function runSetup($identifier) {
    foreach (HashtopolisSetup::getInstances() as $instance) {
      if ($instance->getIdentifier() == $identifier) {
        if (!$instance->isApplicable()) {
          throw new HTException("This tool is not applicable to current setup!");
        }
        $instance->execute([]);
        return;
      }
    }
  }
}