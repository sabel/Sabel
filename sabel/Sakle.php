<?php

require_once('Sabel/Sabel.php');

$args = $_SERVER['argv'];
$pathToSabel = dirname(dirname($args[0]));
$includePath = get_include_path();
if (!in_array($pathToSabel, explode(':', $includePath))) {
  set_include_path(get_include_path().':'.$pathToSabel);
}

Sakle::main($args[1]);

class Sakle
{
  const MSG_INFO = 0x01;
  const MSG_WARN = 0x05;
  const MSG_ERR  = 0x0A;
  
  protected $messageHeaders = array(self::MSG_INFO => "[\x1b[1;32mSUCCESS\x1b[m]",
                                    self::MSG_WARN => "[\x1b[1;33mWARNING\x1b[m]",
                                    self::MSG_ERR  => "[\x1b[1;31mERROR\x1b[m]");
  
  protected $runningDirectory = '';
  
  protected $arguments = array();
  
  public static function main($class)
  {
    $instance = new self();
    $instance->run($class);
  }
  
  public function __construct()
  {
    // @todo compatibility for Windows
    $this->runningDirectory = getcwd();
    $args = $_SERVER['argv'];
    array_shift($args);
    $this->arguments = $args;
  }
  
  public function run($class)
  {
    $pathToClass = $this->runningDirectory . '/tasks/' . $class . '.php';
    if (is_readable($pathToClass)) {
      require ($pathToClass); 
      $ins = new $class();
      $ins->execute();
    }
  }
  
  protected function printMessage($msg, $type = self::MSG_INFO)
  {
    switch ($type) {
      case self::MSG_INFO:
        echo $this->messageHeaders[self::MSG_INFO] .': '. $msg . "\n";
        break;
      case self::MSG_WARN:
        echo $this->messageHeaders[self::MSG_WARN] .': '. $msg . "\n";
        break;
      case self::MSG_ERR:
        echo $this->messageHeaders[self::MSG_ERR]  .': '. $msg . "\n";
        break;
    }
  }
  
  protected function stop()
  {
    exit;
  }
}
