<?php

$VERSION = "1.0.0-rainbow";
$BUILD = "repository";
$HOST = @$_SERVER['HTTP_HOST'];
if (strpos($HOST, ":") !== false) {
  $HOST = substr($HOST, 0, strpos($HOST, ":"));
}
