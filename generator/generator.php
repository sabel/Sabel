<?php

require_once('Sabel/Sabel.php');
require_once('classes.php');

$dt = new DirectoryTraverser(dirname(__FILE__) . '/skeleton');
$dt->visit(new SabelDirectoryAndFileCreator());
$dt->traverse();
