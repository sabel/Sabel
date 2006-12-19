<?php

define('PRODUCTION',  0x01);
define('TEST',        0x05);
define('DEVELOPMENT', 0x0A);

/**
 * define sabel environment.
 */
// if (!defined('ENVIRONMENT')) define('ENVIRONMENT', PRODUCTION);
// if (!defined('ENVIRONMENT')) define('ENVIRONMENT', TEST);
if (!defined('ENVIRONMENT')) define('ENVIRONMENT', DEVELOPMENT);

/**
 * error_reporting settings.
 */
if (ENVIRONMENT === DEVELOPMENT) {
  error_reporting(E_ALL|E_STRICT);
} else {
  error_reporting(0);
}

add_include_path('/app');
add_include_path('/app/models');
add_include_path('/lib');

define("__TRUE__",  "_true");
define("__FALSE__", "_false");