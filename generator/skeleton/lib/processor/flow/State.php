<?php

class Processor_Flow_State
{
  private $currentActivity = "";
  private $nextActivities = array();
  private $token = "";
  
  private $storage = null;
  private $properties = array();
  private $next = "";
  
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
    return ($this->token !== "");
  }
  
  public function getToken()
  {
    return $this->token;
  }
  
  public function restore($token)
  {
    $this->token = $token;
    return $this->storage->read($this->getStateKey());
  }
  
  public function save()
  {
    $this->storage->write($this->getStateKey(), $this);
  }
  
  public function end()
  {
    $this->storage->delete($this->getStateKey());
  }
  
  public function setNextAction($action)
  {
    $this->next = $action;
  }
  
  public function isMatchToNext($currenctAction)
  {
    return ($this->next === $currenctAction);
  }
  
  public function getNextAction()
  {
    return $this->next;
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
  
  public function getStateKey()
  {
    return "flow_state_" . $this->token;
  }
}
