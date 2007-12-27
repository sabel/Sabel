<?php

/**
 * Flow_State
 *
 * @version    1.0
 * @category   Addon
 * @package    addon.flow
 * @author     Mori Reo <mori.reo@gmail.com>
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Mori Reo <mori.reo@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Flow_State
{
  const SES_TIMEOUT = 900;
  
  private $properties = array();
  
  public function __construct($token)
  {
    $this->properties["token"] = $token;
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
  
  public function start($key, $activity, $token)
  {
    $p =& $this->properties;
    
    $p["key"]   = $key;
    $p["token"] = $token;
    $p["currentActivity"] = $activity;
  }
  
  public function isInFlow()
  {
    return ($this->properties["token"] !== "");
  }
  
  public function transit($action)
  {
    $p =& $this->properties;
    
    $p["previousActivity"] = $p["currentActivity"];
    $p["currentActivity"]  = $action;
  }
  
  public function getCurrent()
  {
    return $this->properties["currentActivity"];
  }
  
  public function restore($storage, $key)
  {
    $properties = $storage->read($this->getStateKey($key));
    
    if ($properties === null) {
      return null;
    } else {
      $this->properties = $properties;
      return $this;
    }
  }
  
  public function save($storage)
  {
    $storage->write($this->getStateKey(), $this->properties, self::SES_TIMEOUT);
  }
  
  public function setNextActions($actions)
  {
    $this->properties["nexts"] = $actions;
  }
  
  public function isMatchToNext($currentAction)
  {
    return (in_array($currentAction, $this->properties["nexts"]));
  }
  
  public function isPreviousAction($action)
  {
    return ($this->properties["previousActivity"] === $action);
  }
  
  public function getStateKey($key = "")
  {
    $token = $this->properties["token"];
    
    if ($key !== "") {
      return $key . "_flow_state_" . $token;
    } else {
      return $this->key . "_flow_state_" . $token;
    }
  }
}
