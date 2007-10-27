<?php

if (!defined("PRODUCTION"))  define("PRODUCTION",  0x01);
if (!defined("TEST"))        define("TEST",        0x05);
if (!defined("DEVELOPMENT")) define("DEVELOPMENT", 0x0A);

add_include_paths(array("app",
                        "lib",
                        "app" . DS. "models",
                        "app" . DS. "helpers"));

set_include_path(Sabel::getPath() . ":" . get_include_path());

Sabel::fileUsing("config" . DS . "defines.php", true);
Sabel::fileUsing("config" . DS . "Bus.php", true);
Sabel::fileUsing("config" . DS . "Map.php", true);
Sabel::fileUsing("config" . DS . "Factory.php", true);
Sabel::fileUsing("config" . DS . "connection.php", true);
