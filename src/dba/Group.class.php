<?php

namespace DBA;

abstract class Group {
  abstract function getQueryString($table = "");
}