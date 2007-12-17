<?php

class Flow_State
{
  const END_FLOW_SESKEY = "sbl_end_flows";
  
  private
    $key   = "",
    $token = "";
  
  private
    $storage    = null,
    $properties = array(),
    $nexts      = array();
    
  private
    $previousActivity = "",
    $currentActivity  = "";
    
  public function __construct($storage)
  {
    $this->storage = $storage;
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
  
  public function has($name)
  {
    return (isset($this->properties[$name]));
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
    return $this->getProperties();
  }
  
  public function start($key, $activity, $token)
  {
    $this->key = $key;
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
    $this->previousActivity = $this->currentActivity;
    $this->currentActivity  = $action;
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
  
  public function setNextActions($actions)
  {
    $this->nexts = $actions;
  }
  
  public function isMatchToNext($currentAction)
  {
    return (in_array($currentAction, $this->nexts));
  }
  
  public function isPreviousAction($action)
  {
    return ($this->previousActivity === $action);
  }
  
  public function end()
  {
    $storage = $this->storage;
    if (($ends = $storage->read(self::END_FLOW_SESKEY)) === null) {
      $ends = array($this->getStateKey());
    } else {
      $ends[] = $this->getStateKey();
    }
    
    $this->storage->write(self::END_FLOW_SESKEY, $ends);
  }
  
  public function clearEndFlow()
  {
    $ends = $this->storage->read(self::END_FLOW_SESKEY);
    if ($ends === null) return;
    
    foreach ($ends as $seskey) {
      $this->storage->delete($seskey);
    }
    
    $this->storage->delete(self::END_FLOW_SESKEY);
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
