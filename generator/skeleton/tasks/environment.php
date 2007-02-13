<?php

if (!defined('PRODUCTION'))  define('PRODUCTION',  0x01);
if (!defined('TEST'))        define('TEST',        0x05);
if (!defined('DEVELOPMENT')) define('DEVELOPMENT', 0x0A);

add_include_path('/app');
add_include_path('/app/models');
add_include_path('/lib');
add_include_path("/components");
add_include_path("/tests");

define("__TRUE__",  "true");
define("__FALSE__", "false");