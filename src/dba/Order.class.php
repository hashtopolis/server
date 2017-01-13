<?php

/**
 * Created by IntelliJ IDEA.
 * User: sein
 * Date: 03.01.17
 * Time: 14:49
 */

namespace DBA;

abstract class Order {
  abstract function getQueryString($table = "");
}