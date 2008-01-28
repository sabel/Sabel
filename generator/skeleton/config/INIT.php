<?php

require (RUN_BASE . DIRECTORY_SEPARATOR . "config" . DIRECTORY_SEPARATOR . "defines.php");

unshift_include_paths(array(MODULES_DIR_NAME,
                            LIB_DIR_NAME,
                            MODULES_DIR_NAME . DS . "models",
                            ADDON_DIR_NAME), RUN_BASE . DS);

set_include_path(Sabel::getPath() . PATH_SEPARATOR . get_include_path());

/**
 * include config files.
 */
Sabel::fileUsing(CONFIG_DIR_PATH . DIRECTORY_SEPARATOR . "Bus.php", true);
Sabel::fileUsing(CONFIG_DIR_PATH . DIRECTORY_SEPARATOR . "Map.php", true);
