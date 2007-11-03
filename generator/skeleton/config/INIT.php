<?php

Sabel::fileUsing(RUN_BASE . DS . "config" . DS . "defines.php", true);

add_include_paths(array(MODULES_DIR_NAME,
                        LIB_DIR_NAME,
                        MODULES_DIR_NAME . DS . "models",
                        ADDON_DIR_NAME));

set_include_path(Sabel::getPath() . ":" . get_include_path());

/**
 * include config files.
 */
Sabel::fileUsing(CONFIG_DIR_PATH . DS . "Bus.php", true);
Sabel::fileUsing(CONFIG_DIR_PATH . DS . "Map.php", true);
Sabel::fileUsing(CONFIG_DIR_PATH . DS . "Factory.php", true);
Sabel::fileUsing(CONFIG_DIR_PATH . DS . "connection.php", true);
