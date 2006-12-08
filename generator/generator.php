<?php

require_once('Sabel/Sabel.php');
require_once('classes.php');

$dt = new DirectoryTraverser(dirname(__FILE__) . '/skeleton');

$args = $_SERVER['argv'];
if (isset($args[1])) {
  $overwrite = true;
} else {
  $overwrite = false;
}

$dt->visit(new SabelDirectoryAndFileCreator($overwrite));
$dt->traverse();