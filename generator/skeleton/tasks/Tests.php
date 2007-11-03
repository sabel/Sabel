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
    $index = array_search("-e", $this->arguments, true);
    if ($index === false) return TEST;
    
    if (isset($this->arguments[$index + 1])) {
      $environment = environment($this->arguments[$index + 1]);
      unset($this->arguments[$index]);
      unset($this->arguments[$index + 1]);
      return $environment;
    }
  }
}
