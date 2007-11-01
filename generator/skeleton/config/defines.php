<?php

if (!defined("PRODUCTION"))  define("PRODUCTION",  0x01);
if (!defined("TEST"))        define("TEST",        0x05);
if (!defined("DEVELOPMENT")) define("DEVELOPMENT", 0x0A);

define("TPL_CACHE_DIR",   "cache");
define("TPL_COMPILE_DIR", "data" . DS . "compiled");

define("MODULES_DIR", "app");
define("ADDON_DIR",   "addon");
define("MODELS_DIR",  MODULES_DIR . DS . "models");

define("DEFAULT_LAYOUT_NAME", "layout");

define("VIEW_DIR", "views");
define("APP_VIEW", MODULES_DIR . DS . VIEW_DIR . DS);
