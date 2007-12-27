<?php

require_once ("Sabel" . DIRECTORY_SEPARATOR . "Sabel.php");
define("RUN_BASE", getcwd());
Sabel::fileUsing(RUN_BASE . DS . "config" . DS . "INIT.php", true);

/**
 * Sakle
 *
 * @category   Sakle
 * @package    org.sabel.sakle
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sakle
{
  protected $arguments = array();
  
  public function __construct()
  {
    $args = $_SERVER["argv"];
    array_shift($args);
    $this->arguments = $args;
  }
  
  public function run($class)
  {
    $pathToClass = RUN_BASE . DS . "tasks" . DS . $class . PHP_SUFFIX;
    
    if (is_readable($pathToClass)) {
      Sabel::fileUsing($pathToClass, true);
      
      $ins  = new $class();
      $args = $this->arguments;
      
      if (isset($args[1])) {
        if ($args[1] === "-h" || $args[1] === "--help") {
          $ins->usage();
          exit;
        }
      }
      
      try {
        if ($ins->hasMethod("initialize")) {
          $ins->initialize($this->arguments);
        }
        
        $ins->run($this->arguments);
        
        if ($ins->hasMethod("finalize")) {
          $ins->finalize();
        }
      } catch (Exception $e) {
        Sabel_Command::error($e->getMessage());
      }
    } else {
      Sabel_Command::error("such a task doesn't exist.");
    }
  }
  
  public static function main($class)
  {
    $instance = new self();
    $instance->run($class);
  }
}

$pathToSabel = Sabel::getPath();
$includePath = get_include_path();

if (!in_array($pathToSabel, explode(PATH_SEPARATOR, $includePath))) {
  set_include_path($includePath . PATH_SEPARATOR . $pathToSabel);
}

if (isset($_SERVER["argv"][1])) {
  Sakle::main($_SERVER["argv"][1]);
} else {
  echo "@todo error";
}
