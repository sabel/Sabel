<?php

if(defined('SABEL_USE_INCLUDE_PATH')) {
  require_once('Sabel/sabel/Functions.php');
  require_once('Sabel/sabel/core/Context.php');
  require_once('Sabel/sabel/logger/File.php');
  Sabel_Core_Context::addIncludePath('Sabel/');
  require_once('Sabel/Sabel.php');
} else {
  require_once(RUN_BASE . '/Sabel/sabel/Functions.php');
  require_once(RUN_BASE . '/Sabel/sabel/core/Context.php');
  Sabel_Core_Context::addIncludePath(RUN_BASE . '/Sabel/');
  require_once(RUN_BASE . '/Sabel/Sabel.php');
}