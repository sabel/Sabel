<?php

/**
 * Flow_State
 *
 * @category   Addon
 * @package    addon.flow
 * @author     Mori Reo <mori.reo@sabel.jp>
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Flow_State
{
  const MAX_LIFETIME = 1200;
  
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
    return array_key_exists($name, $this->properties);
  }
  
  public function __set($name, $value)
  {
    $this->write($name, $value);
  }
  
  public function __get($name)
  {
    return $this->read($name);
  }
  
  public function getProperties()
  {
    return $this->properties;
  }
  
  public function restore($properties)
  {
    $this->properties = $properties;
  }
  
  public function transit($action)
  {
    $p =& $this->properties;
    
    $p["previousActivity"] = $p["currentActivity"];
    $p["currentActivity"]  = $action;
  }
  
  public function setCurrentActivity($activity)
  {
    $this->properties["currentActivity"] = $activity;
  }
  
  public function getCurrentActivity()
  {
    return $this->properties["currentActivity"];
  }
  
  public function save(Sabel_Token_Storage $storage, $timeout = null)
  {
    if ($timeout === null) $timeout = self::MAX_LIFETIME;
    $storage->store($this->properties["token"], $this->properties, $timeout);
  }
  
  public function setNextActions($actions)
  {
    $this->properties["nexts"] = $actions;
  }
  
  public function isMatchToNext($currentAction)
  {
    return in_array($currentAction, $this->properties["nexts"], true);
  }
  
  public function isPreviousAction($action)
  {
    if ($this->has("previousActivity")) {
      return ($this->properties["previousActivity"] === $action);
    } else {
      return false;
    }
  }
}
