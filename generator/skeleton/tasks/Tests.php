<?php

abstract class Tests extends Sabel_Sakle_Task
{
  protected $arguments = array();
  
  public function initialize($arguments)
  {
    $this->arguments = $arguments;
    $environment = $this->getEnvironment();
    
    if ($environment === null) {
      throw new Exception("environment must be specified.");
    } else {
      define ("ENVIRONMENT", $environment);
    }
    
    if (ENVIRONMENT === PRODUCTION) {
      error_reporting(0);
    } else {
      error_reporting(E_ALL|E_STRICT);
    }
  }
  
  protected function getEnvironment()
  {
    $args  = $this->arguments;
    $index = array_search("-e", $args, true);
    if ($index === false) return TEST;
    
    if (isset($args[$index + 1])) {
      $environment = environment($args[$index + 1]);
      unset($args[$index]);
      unset($args[$index + 1]);
      $this->arguments = array_values($args);
      return $environment;
    }
  }
}
