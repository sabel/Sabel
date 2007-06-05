<?php

if (!defined('PRODUCTION'))  define('PRODUCTION',  0x01);
if (!defined('TEST'))        define('TEST',        0x05);
if (!defined('DEVELOPMENT')) define('DEVELOPMENT', 0x0A);

add_include_path('/app');
add_include_path('/app/models');
add_include_path('/lib');
add_include_path("/components");
add_include_path("/tests");

require (RUN_BASE . "/config/Factory.php");
require (RUN_BASE . "/config/Map.php");