<?php

require_once ("Sabel/Sabel.php");

$args = $_SERVER["argv"];
$pathToSabel = dirname(dirname($args[0]));
$includePath = get_include_path();

if (!in_array($pathToSabel, explode(":", $includePath))) {
  set_include_path(get_include_path() . ":" . $pathToSabel);
}

if (isset($args[1])) {
  Sakle::main($args[1]);
} else {
  Sakle::main(null);
}

class Sakle
{
  const MSG_INFO = 0x01;
  const MSG_WARN = 0x05;
  const MSG_ERR  = 0x0A;
  
  protected $messageHeaders = array(self::MSG_INFO => "[\x1b[1;32mSUCCESS\x1b[m]",
                                    self::MSG_WARN => "[\x1b[1;33mWARNING\x1b[m]",
                                    self::MSG_ERR  => "[\x1b[1;31mERROR\x1b[m]");
  
  protected $runningDirectory = "";
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
    $this->runningDirectory = getcwd();
    $args = $_SERVER["argv"];
    array_shift($args);
    $this->arguments = $args;
  }
  
  public function run($class)
  {
    $pathToClass = $this->runningDirectory . DIR_DIVIDER
                 . "tasks" . DIR_DIVIDER . $class . ".php";
                 
    if (is_readable($pathToClass)) {
      require ($pathToClass);
      $ins = new $class();
      $ins->run($this->arguments);
      
      if (method_exists($ins, "finalize")) {
        $ins->finalize();
      }
    }
  }
  
  public function allTestRun()
  {
    $pathToClass = $this->runningDirectory . DIR_DIVIDER
                 . "tasks" . DIR_DIVIDER . "TestSuite.php";
                 
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
