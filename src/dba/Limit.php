<?php

namespace Hashtopolis\dba;

abstract class Limit {
  abstract function getQueryString(): string;
}