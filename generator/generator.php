<?php

if (!defined("TEST_CASE")) {

if (!defined("DS")) define("DS", DIRECTORY_SEPARATOR);
if (!defined("IS_WIN")) define("IS_WIN", (DS === '\\'));

$args = $_SERVER["argv"];

if (isset($args[1])) {
  if (strpos($args[1], "-") === false) {
    $dir = getcwd() . DS . $args[1];
  }
} else {
  $dir = getcwd();
}

if (!defined("RUN_BASE")) {
  define("RUN_BASE", $dir);
}

if (!is_dir(RUN_BASE)) mkdir(RUN_BASE);
define("ENVIRONMENT", 0x0A);

require ("Sabel" . DIRECTORY_SEPARATOR . "Sabel.php");
require ("classes.php");

$pathToSabel = Sabel::getPath();
$includePath = get_include_path();

if (!in_array($pathToSabel, explode(PATH_SEPARATOR, $includePath))) {
  set_include_path($includePath . PATH_SEPARATOR . $pathToSabel);
}

$dt = new Sabel_Util_DirectoryTraverser(dirname(__FILE__) . DIRECTORY_SEPARATOR . "skeleton");
$aCreator = new SabelDirectoryAndFileCreator();

for ($i = 0, $count = count($args); $i < $count; ++$i) {
  if ($args[$i] === "--overwrite") {
    $aCreator->setOverwrite(true);
  } elseif ($args[$i] === "--ignore") {
    $fwArg = $args[$i+1];
    if (isset($fwArg) && stripos($fwArg, "--") === false) {
      if (stripos($fwArg, ",") !== false) {
        $ignores = explode(",", $fwArg);
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

} // end of !defined("TEST_CASE")
