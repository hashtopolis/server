<?php

namespace DBA;

abstract class Order {
  abstract function getQueryString(AbstractModelFactory $factory, bool $includeTable = false): string;
}