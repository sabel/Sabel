<?php

/**
 * define sabel environment.
 */
//define('ENVIRONMENT', 'production');
//define('ENVIRONMENT', 'test');
define('ENVIRONMENT', 'development');

/**
 * error_reporting settings.
 */
if (ENVIRONMENT === 'development') {
  error_reporting(E_ALL|E_STRICT);
} else {
  error_reporting(0);
}