<?php

if (!defined('PRODUCTION'))  define('PRODUCTION',  0x01);
if (!defined('TEST'))        define('TEST',        0x05);
if (!defined('DEVELOPMENT')) define('DEVELOPMENT', 0x0A);

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
  assert_options(ASSERT_ACTIVE,     1);
  assert_options(ASSERT_WARNING,    0);
  assert_options(ASSERT_QUIET_EVAL, 1);

  function assert_handler($file, $line, $code)
  {
    echo "<hr>Assertion Failed: <br/>";
    echo "File:\t '$file'<br />";
    echo "Line:\t '$line'<br />";
    echo "Code:\t '$code'<br /><hr />";
  }
  assert_options(ASSERT_CALLBACK, 'assert_handler');
} else {
  error_reporting(0);
  assert_options(ASSERT_ACTIVE,     0);
  assert_options(ASSERT_WARNING,    0);
  assert_options(ASSERT_QUIET_EVAL, 0);
}

add_include_path('/app');
add_include_path('/app/models');
add_include_path('/lib');

define("__TRUE__",  "true");
define("__FALSE__", "false");