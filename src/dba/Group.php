<?php

namespace Hashtopolis\dba;

abstract class Group {
  abstract function getQueryString(AbstractModelFactory $factory, bool $includeTable = false);
}