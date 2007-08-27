<?php

if (!defined("PRODUCTION"))  define("PRODUCTION",  0x01);
if (!defined("TEST"))        define("TEST",        0x05);
if (!defined("DEVELOPMENT")) define("DEVELOPMENT", 0x0A);

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

add_include_paths(array(DIRECTORY_SEPARATOR . "app",
                        DIRECTORY_SEPARATOR . "lib",
                        DIRECTORY_SEPARATOR . "config",
                        DIRECTORY_SEPARATOR . "app" . DIRECTORY_SEPARATOR . "helpers"));

function add_include_path($path)
{
  set_include_path(RUN_BASE . "{$path}:" . get_include_path());
}

function add_include_paths($paths)
{
  $path = "";
  foreach ($paths as $p) $path .= RUN_BASE . $p . ":";
  set_include_path($path . get_include_path());
}
