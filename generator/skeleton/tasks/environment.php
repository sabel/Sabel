<?php

Sabel::fileUsing("config" . DS . "defines.php", true);

add_include_paths(array(MODULES_DIR,
                        "lib",
                        ADDON_DIR,
                        MODELS_DIR,
                        MODULES_DIR . DS. "helpers"));

set_include_path(Sabel::getPath() . ":" . get_include_path());

Sabel::fileUsing("config" . DS . "Bus.php", true);
Sabel::fileUsing("config" . DS . "Map.php", true);
Sabel::fileUsing("config" . DS . "Factory.php", true);
Sabel::fileUsing("config" . DS . "connection.php", true);
