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
  define("SBL_LOG_LEVEL", SBL_LOG_ALL);
} else {
  error_reporting(0);
  define("SBL_LOG_LEVEL", SBL_LOG_ERR);
}
