<?php

namespace APIv2;

class GetHashType {
  /**
   * @var {@type int} {@min 0}
   */
  public $id;
  /**
   * @var {@type string}
   */
  public $description;
  /**
   * @var {@type bool} {@default false}
   */
  public $isSalted;
  /**
   * @var {@type bool} {@default false}
   */
  public $isSlowHash;
}