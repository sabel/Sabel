<?php

define("PRODUCTION",  0x01);
define("TEST",        0x05);
define("DEVELOPMENT", 0x0A);

define("PHP_SUFFIX", ".php");
define("TPL_SUFFIX", ".tpl");

define("MODULES_DIR_NAME", "app");
define("ADDON_DIR_NAME",   "addon");
define("VIEW_DIR_NAME",    "views");
define("LIB_DIR_NAME",     "lib");
define("HELPERS_DIR_NAME", "helpers");

define("CONFIG_DIR_PATH",  RUN_BASE . DS . "config");
define("MODULES_DIR_PATH", RUN_BASE . DS . MODULES_DIR_NAME);
define("ADDON_DIR_PATH",   RUN_BASE . DS . ADDON_DIR_NAME);
define("MODELS_DIR_PATH",  MODULES_DIR_PATH . DS . "models");
define("SCHEMA_DIR_PATH",  RUN_BASE . DS . LIB_DIR_NAME . DS . "schema");
define("CACHE_DIR_PATH",   RUN_BASE . DS . "cache");
define("COMPILE_DIR_PATH", RUN_BASE . DS . "data" . DS . "compiled");
define("TEST_DIR_PATH",    RUN_BASE . DS . "tests");

define("DEFAULT_LAYOUT_NAME", "layout");
