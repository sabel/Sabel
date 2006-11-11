<?php

/**
 * define sabel environment.
 */
// if (!defined('ENVIRONMENT')) define('ENVIRONMENT', 'production');
// if (!defined('ENVIRONMENT')) define('ENVIRONMENT', 'test');
if (!defined('ENVIRONMENT')) define('ENVIRONMENT', 'development');

/**
 * error_reporting settings.
 */
if (ENVIRONMENT === 'development') {
  error_reporting(E_ALL|E_STRICT);
} else {
  error_reporting(0);
}