<?php

require_once('Sabel/Container.php');
require_once('classes.php');

$dt = new DirectoryTraverser(dirname(__FILE__) . '/skeleton');
$dt->visit(new SabelDirectoryAndFileCreator());
$dt->traverse();
