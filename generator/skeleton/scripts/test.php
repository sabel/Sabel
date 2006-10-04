#!/opt/local/php/bin/php
<?php

require_once('setup.php');

$arguments = $_SERVER['argv'];
if (isset($arguments[2])) {
  $module     = $arguments[1];
  $controller = $arguments[2];
} else {
  $module     = null;
  $controller = $arguments[1];
}

Sabel_Test_Runner::running($controller, $module);