<?php

namespace APIv2;

class GetHealth {
  /**
   * @var {@type string} {@default UP}
   */
  public $status;

  public static function create($status) {
    $dto = new GetHealth();
    $dto->status = $status;
    return $dto;
  }
}