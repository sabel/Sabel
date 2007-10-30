<?php

class Flow_State
{
  private $key = "";
  private $currentActivity = "";
  private $nextActivities = array();
  private $endAction = "";
  private $token = "";
  
  private $storage = null;
  private $properties = array();
  private $nexts = array();
  
  public function __construct($storage)
  {
    $this->storage = $storage;
  }
  
  public function start($key, $activity, $token)
  {
    $this->key = $key;
    $this->currentActivity = $activity;
    $this->token = $token;
  }
  
  public function setEndAction($endAction)
  {
    $this->endAction = $endAction;
  }
  
  public function isEndAction($action)
  {
    return ($this->endAction === $action);
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
  
  public function restore($key, $token)
  {
    $this->token = $token;
    return $this->storage->read($this->getStateKey($key));
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
  
  public function has($name)
  {
    return (isset($this->properties[$name]));
  }
  
  public function read($name)
  {
    if ($this->has($name)) {
      return $this->properties[$name];
    } else {
      return null;
    }
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
  
  public function getProperties()
  {
    return $this->properties;
  }
  
  public function toArray()
  {
    return $this->properties;
  }
  
  public function getStateKey($key = "")
  {
    if ($key !== "") {
      return $key . "_flow_state_" . $this->token;
    } else {
      return $this->key . "_flow_state_" . $this->token;
    }
  }
}
