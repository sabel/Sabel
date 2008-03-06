<?php

##################### ENVIRONMENTS ########################

define("PRODUCTION",  0x01);
define("TEST",        0x05);
define("DEVELOPMENT", 0x0A);

################### SABEL LOG LEVELS ######################

define("SBL_LOG_INFO",  0x01);
define("SBL_LOG_DEBUG", 0x02);
define("SBL_LOG_WARN",  0x03);
define("SBL_LOG_ERR",   0x04);
define("SBL_LOG_ALL",   0xFF);

############################################################

define("TPL_SUFFIX", ".tpl");
define("DS", DIRECTORY_SEPARATOR);

define("MODULES_DIR_NAME", "app");
define("ADDON_DIR_NAME",   "addon");
define("VIEW_DIR_NAME",    "views");
define("LIB_DIR_NAME",     "lib");
define("HELPERS_DIR_NAME", "helpers");

define("CONFIG_DIR_PATH",  RUN_BASE . DS . "config");
define("MODULES_DIR_PATH", RUN_BASE . DS . MODULES_DIR_NAME);
define("MODELS_DIR_PATH",  MODULES_DIR_PATH . DS . "models");
define("LOG_DIR_PATH",     RUN_BASE . DS . "logs");
define("CACHE_DIR_PATH",   RUN_BASE . DS . "cache");
define("COMPILE_DIR_PATH", RUN_BASE . DS . "data" . DS . "compiled");

define("DEFAULT_LAYOUT_NAME", "layout");

################# INCLUDE_PATH SETTINGS ####################

unshift_include_paths(array(MODULES_DIR_NAME,
                            LIB_DIR_NAME,
                            MODULES_DIR_NAME . DS . "models",
                            ADDON_DIR_NAME), RUN_BASE . DS);

unshift_include_path(Sabel::getPath());

############### INCLUDE CONFIGURATION FILES ################

Sabel::fileUsing(CONFIG_DIR_PATH . DS . "Bus.php",      true);
Sabel::fileUsing(CONFIG_DIR_PATH . DS . "Map.php",      true);
Sabel::fileUsing(CONFIG_DIR_PATH . DS . "Addon.php",    true);
Sabel::fileUsing(CONFIG_DIR_PATH . DS . "Database.php", true);

