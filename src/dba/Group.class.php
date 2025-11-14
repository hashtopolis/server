<?php

namespace DBA;

abstract class Group {
  abstract function getQueryString(AbstractModelFactory $factory, bool $includeTable = false);
}