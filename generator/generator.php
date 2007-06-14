<?php

require_once ('Sabel/Sabel.php');
require_once ('classes.php');

$args = $_SERVER['argv'];

define(RUN_BASE, getcwd());

$pathToSabel = dirname(dirname($args[0]));
$includePath = get_include_path();
if (!in_array($pathToSabel, explode(':', $includePath))) {
  set_include_path(get_include_path().':'.$pathToSabel);
}

$dt = new Sabel_Util_DirectoryTraverser(dirname(__FILE__) . '/skeleton');
$aCreator = new SabelDirectoryAndFileCreator();

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