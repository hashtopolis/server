<?php

namespace APIv2;

abstract class BaseNormalTask {
  /**
   * @var {@type int} {@min 0}
   */
  public $priority;
  /**
   * @var {@type int} {@min 0}
   */
  public $maxAgents;
}

class GetNormalTask {
  /**
   * @var {@type int} {@min 0}
   */
  public $id;
  /**
   * @var {@type string}
   */
  public $name;
    /**
   * @var {@type int} {@min 0}
   */
  public $hashlistId;

  // TODO: add more necessary properties
}

class UpdateNormalTask extends BaseNormalTask {
  // empty for now
}

class GetSuperTask {
  /**
   * @var {@type int} {@min 0}
   */
  public $id;
  /**
   * @var {@type string}
   */
  public $name;
  /**
   * @var {@type int} {@min 0}
   */
  public $priority;
  /**
   * @var {@type int} {@min 0}
   */
  public $crackedCount;
  /**
   * @var {@type int} {@min 0}
   */
  public $hashlistId;
  /**
   * @var {@type int} {@min 1} {@default 1}
   */
  public $accessGroupId;
  /**
   * @var {@type bool} {@default false}
   */
  public $isArchived;
}