<?php

class Processor_Flow_State
{
  private $currentActivity = "";
  private $nextActivities = array();
  private $token = "";
  
  private $storage = null;
  private $properties = array();
  
  public function __construct($storage)
  {
    $this->storage = $storage;
  }
  
  public function start($activity, $token)
  {
    $this->currentActivity = $activity;
    $this->token = $token;
  }
  
  public function isInFlow()
  {
    
  }
  
  public function restore($token)
  {
    $stateKey = "flow_state_" . $token;
    
    if ($this->storage->has($stateKey)) {
      return $this->storage->read($stateKey);
    } else {
      $this->storage->write($stateKey, $this);
    }
  }
  
  public function read($name)
  {
    return $this->properties[$name];
  }
  
  public function write($name, $value)
  {
    $this->properties[$name] = $value;
  }
  
  public function __get($name)
  {
    return $this->read($name);
  }
  
  public function __set($name, $value)
  {
    $this->write($name, $value);
  }
}
