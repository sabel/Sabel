<?php

require_once ("Sabel/Sabel.php");
define("RUN_BASE", getcwd());
Sabel::fileUsing(RUN_BASE . DS . "config" . DS . "INIT.php", true);

class Sakle
{
  protected $arguments = array();
  
  public function __construct()
  {
    // @todo compatibility for Windows
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
        Sabel_Sakle_Task::error($e->getMessage());
      }
    } else {
      Sabel_Sakle_Task::error("such a task doesn't exist.");
    }
  }
  
  public function allTestRun()
  {
    $pathToClass = RUN_BASE . DS . "tasks" . DS . "TestSuite.php";
    
    if (is_readable($pathToClass)) {
      require ($pathToClass);
      $ins = new TestSuite();
      $ins->run($this->arguments);
    }
  }
  
  public static function main($class = null)
  {
    $instance = new self();
    
    if ($class === null) {
      $instance->allTestRun();
    } else {
      $instance->run($class);
    }
  }
}

$pathToSabel = Sabel::getPath();
$includePath = get_include_path();

if (!in_array($pathToSabel, explode(PATH_SEPARATOR, $includePath))) {
  set_include_path($includePath . PATH_SEPARATOR . $pathToSabel);
}

isset($_SERVER["argv"][1]) ? Sakle::main($_SERVER["argv"][1]) : Sakle::main();

