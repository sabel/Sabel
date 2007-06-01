<?php

/**
 * Sabel_Controller_Flow
 *
 * @category   Flow
 * @package    org.sabel.controller
 * @author     Mori Reo <mori.reo@gmail.com>
 * @copyright  2002-2006 Mori Reo <mori reo@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
abstract class Sabel_Plugin_Flow_Config
{
  private
    $entryActivity   = null,
    $currentActivity = null,
    $endActivity     = null;
    
  private
    $inflow = false;
    
  private
    $activities    = array(),
    $flowVariables = array();
  
  abstract function configure();
    
  public function activity($name)
  {
    return new Sabel_Plugin_Flow_Activity($name);
  }
  
  public function activities()
  {
    $activities = array();
    
    $args = func_get_args();
    for ($i = 0; $i < func_num_args(); $i++) {
      $activities[] = new Sabel_Plugin_Flow_Activity($args[$i]);
    }
    
    return $activities;
  }
  
  public function getActivities()
  {
    return $this->activities;
  }
  
  public function isActivity($action)
  {
    foreach ($this->getActivities() as $activity) {
      if ($activity->hasEvent($action)) {
        return true;
      }
    }
    return (in_array($action, array_keys($this->getActivities())));
  }
  
  public function entry($activity)
  {
    $this->entryActivity = $activity;
    return $this->add($activity);
  }
  
  public function add($activity)
  {
    $this->activities[$activity->getName()] = $activity;
    return $this;
  }
  
  public function end($activity)
  {
    $this->endActivity = $activity;
    return $this->add($activity);
  }
  
  public function isInFlow()
  {
    return $this->inflow;
  }
  
  public function start($action)
  {
    if ($this->isInFlow()) return false;
    
    if ($this->isEntryActivity($action)) {
      $this->currentActivity = $this->entryActivity;
      $this->inflow = true;
      return true;
    } else {
      return false;
    }
  }
  
  public function isEntryActivity($action)
  {
    return ($this->entryActivity->getName() === $action);
  }
  
  public function isEndActivity($action)
  {
    return ($this->endActivity->getName() === $action);
  }
  
  public function canTransitTo($event)
  {
    return ($this->currentActivity->hasEvent($event));
  }
  
  public function transit($event)
  {
    $currentActivity = $this->currentActivity;
    $nextActivity = null;
    
    if ($this->canTransitTo($event)) {
      $nextActivity = $currentActivity->getNextActivity($event);
      $this->currentActivity = $nextActivity;
      
      if ($nextActivity->getName() === $this->endActivity->getName()) {;
        $this->inflow = false;
      }
      
      return $nextActivity;
    } else {
      return false;
    }
  }
  
  public function isCurrent($action)
  {
    return ($this->currentActivity->getName() === $action);
  }
  
  public function getCurrentActivity()
  {
    return $this->currentActivity;
  }
  
  public function __get($key)
  {
    return $this->read($key);
  }
  
  public function __set($key, $value)
  {
    $this->write($key, $value);
  }
  
  public function read($key)
  {
    if (isset($this->flowVariables[$key])) {
      return $this->flowVariables[$key];
    } else {
      return null;
    }
  }
  
  public function write($key, $value)
  {
    $this->flowVariables[$key] = $value;
  }
  
  public function toArray()
  {
    return $this->flowVariables;
  }
}
