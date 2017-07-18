<?php

namespace DBA;

abstract class Order {
  abstract function getQueryString($table = "");
}