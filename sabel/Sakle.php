<?php

if (!defined("TEST_CASE")) {
  define("RUN_BASE", getcwd());
  require_once ("Sabel"  . DIRECTORY_SEPARATOR . "Sabel.php");
  require_once (RUN_BASE . DIRECTORY_SEPARATOR . "config" . DIRECTORY_SEPARATOR . "INIT.php");
  
  $pathToSabel = Sabel::getPath();
  $includePath = get_include_path();
  
  if (!in_array($pathToSabel, explode(PATH_SEPARATOR, $includePath))) {
    set_include_path($includePath . PATH_SEPARATOR . $pathToSabel);
  }
  
  if (isset($_SERVER["argv"][1])) {
    Sakle::run($_SERVER["argv"][1]);
  } else {
    echo "@todo error";
  }
}

/**
 * Sakle
 *
 * @category   Sakle
 * @package    org.sabel.sakle
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sakle
{
  public static function run($class)
  {
    $args = $_SERVER["argv"];
    array_shift($args);
    $class = array_shift($args);
    
    $pathToClass = RUN_BASE . DS . "tasks" . DS . $class . PHP_SUFFIX;
    
    if (is_readable($pathToClass)) {
      Sabel::fileUsing($pathToClass, true);
      
      $ins = new $class();
      $ins->setArguments($args);
      
      if ($args[0] === "-h" || $args[0] === "--help") {
        $ins->usage();
        exit;
      }
      
      try {
        if ($ins->hasMethod("initialize")) {
          $ins->initialize();
        }
        
        $ins->run();
        
        if ($ins->hasMethod("finalize")) {
          $ins->finalize();
        }
      } catch (Exception $e) {
        Sabel_Console::error($e->getMessage());
      }
    } else {
      Sabel_Console::error("such a task doesn't exist.");
    }
  }
}
