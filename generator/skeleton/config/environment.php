<?php

/**
 * define sabel environment.
 */
// if (!defined("ENVIRONMENT")) define("ENVIRONMENT", PRODUCTION);
// if (!defined("ENVIRONMENT")) define("ENVIRONMENT", TEST);
if (!defined("ENVIRONMENT")) define("ENVIRONMENT", DEVELOPMENT);

/**
 * error_reporting settings.
 */
if (ENVIRONMENT === DEVELOPMENT) {
  error_reporting(E_ALL|E_STRICT);
} else {
  error_reporting(0);
}

add_include_paths(array(MODULES_DIR,
                        "lib",
                        MODELS_DIR,
                        MODULES_DIR . DS. "helpers",
                        "addon"));

set_include_path(Sabel::getPath() . ":" . get_include_path());
