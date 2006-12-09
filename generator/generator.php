<?php

require_once('Sabel/Sabel.php');
require_once('classes.php');

$dt = new DirectoryTraverser(dirname(__FILE__) . '/skeleton');
$aCreator = new SabelDirectoryAndFileCreator();

$args = $_SERVER['argv'];
for ($i = 0, $count = count($args); $i < $count; ++$i) {
  if ($args[$i] === '--overwrite') {
    $aCreator->setOverwrite(true);
  } elseif ($args[$i] === '--ignore') {
    $fwArg = $args[$i+1];
    if (isset($fwArg) && stripos($fwArg, '--') === false) {
      if (stripos($fwArg, ',') !== false) {
        $ignores = explode(',', $fwArg);
        foreach ($ignores as $ignore) {
          $aCreator->addIgnore($ignore);
        }
      } else {
        $aCreator->addIgnore($fwArg);
      }
      ++$i;
    } else {
      echo "must specify ignore directory when using --ignore option\n";
      exit;
    }
  }
}

$dt->visit($aCreator);
$dt->traverse();