<?php

namespace Hashtopolis\dba;

abstract class Order {
  abstract function getQueryString(AbstractModelFactory $factory, bool $includeTable = false): string;
}