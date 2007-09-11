<?php

class Processor_Flow_State
{
  private $currentActivity = "";
  private $nextActivities = array();
  private $token = "";
  
  private $storage = null;
  private $properties = array();
  private $nexts = array();
  
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
  
  public function transit($action)
  {
    $this->currentActivity = $action;
  }
  
  public function getCurrent()
  {
    return $this->currentActivity;
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
  
  public function setNextActions($actions)
  {
    $this->nexts = $actions;
  }
  
  public function addNextAction($action)
  {
    $this->nexts[] = $action;
  }
  
  public function isMatchToNext($currentAction)
  {
    return (in_array($currentAction, $this->nexts));
  }
  
  public function getNextActions()
  {
    return $this->nexts;
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
