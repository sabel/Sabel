<?php

require_once ("Sabel/Sabel.php");
define("RUN_BASE", getcwd());
Sabel::fileUsing(RUN_BASE . DS . "config" . DS . "INIT.php", true);

class Sakle
{
  const MSG_INFO = 0x01;
  const MSG_WARN = 0x05;
  const MSG_ERR  = 0x0A;
  
  protected $messageHeaders = array(self::MSG_INFO => "[\x1b[1;32mSUCCESS\x1b[m]",
                                    self::MSG_WARN => "[\x1b[1;33mWARNING\x1b[m]",
                                    self::MSG_ERR  => "[\x1b[1;31mERROR\x1b[m]");
                                    
  protected $arguments = array();
  
  public static function main($class)
  {
    $instance = new self();
    
    if ($class === null) {
      $instance->allTestRun();
    } else {
      $instance->run($class);
    }
  }
  
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
      $ins = new $class();
      
      if ($ins->hasMethod("initialize")) {
        $ins->initialize($this->arguments);
      }
      
      $ins->run($this->arguments);
      
      if ($ins->hasMethod("finalize")) {
        $ins->finalize();
      }
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
  
  protected function stop()
  {
    exit;
  }
}

$pathToSabel = Sabel::getPath();
$includePath = get_include_path();

if (!in_array($pathToSabel, explode(":", $includePath))) {
  set_include_path($includePath . ":" . $pathToSabel);
}

isset($_SERVER["argv"][1]) ? Sakle::main($_SERVER["argv"][1]) : Sakle::main();

