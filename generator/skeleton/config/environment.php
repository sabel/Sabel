<?php

if (!defined("PRODUCTION"))  define("PRODUCTION",  0x01);
if (!defined("TEST"))        define("TEST",        0x05);
if (!defined("DEVELOPMENT")) define("DEVELOPMENT", 0x0A);

/**
 * define sabel environment.
 */
if (!defined("ENVIRONMENT")) define("ENVIRONMENT", PRODUCTION);
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

add_include_paths(array("app",
                        "lib",
                        "app" . DS. "models",
                        "app" . DS. "helpers"));

set_include_path(Sabel::getPath() . ":" . get_include_path());
