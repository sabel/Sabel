<?php

if (!defined("PRODUCTION"))  define("PRODUCTION",  0x01);
if (!defined("TEST"))        define("TEST",        0x05);
if (!defined("DEVELOPMENT")) define("DEVELOPMENT", 0x0A);

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
define("CACHE_DIR_PATH",   RUN_BASE . DS . "cache");
define("COMPILE_DIR_PATH", RUN_BASE . DS . "data" . DS . "compiled");

define("DEFAULT_LAYOUT_NAME", "layout");
