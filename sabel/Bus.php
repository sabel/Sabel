<?php

/**
 * Sabel_Bus
 *
 * @category   Bus
 * @package    org.sabel.bus
 * @author     Mori Reo <mori.reo@gmail.com>
 * @copyright  2002-2006 Mori Reo <mori.reo@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Bus
{
  private $bus  = array();
  private $holder = array();
  
  private $processors = array();
  private $listeners  = array();
  
  public function __construct($data = null)
  {
    if ($data === null) {
      $this->data = new Sabel_Bus_Data();
    } else {
      $this->data = $data;
    }
  }
  
  public function init($data)
  {
    foreach ($data as $name => $value) {
      $this->set($name, $value);
    }
  }
  
  public function addProcessorAsListener($name, $listener)
  {
    $this->listeners[$name] = $listener;
  }
  
  public function addProcessor($name, Sabel_Bus_Processor $processor)
  {
    $this->processors[$name] = $processor;
  }
  
  public function addGroup($name, $processor = null)
  {
    $group = new Sabel_Bus_ProcessorGroup();
    
    if ($processor !== null) {
      $group->add($name, $processor);
    }
    
    $this->processors[$name] = $group;
    
    return $this;
  }
  
  public function replaceGroup($name, $processor = null)
  {
    $group = new Sabel_Bus_ProcessorGroup();
    
    if ($processor !== null) {
      $group->add($name, $processor);
    }
    
    unset($this->processors[$name]);
    $this->processors[$name] = $group;
    
    return $this;
  }
  
  public function getGroup($name)
  {
    if (array_key_exists($name, $this->processors)) {
      return $this->processors[$name];
    } else {
      throw new Sabel_Exception_Runtime("group not found");
    }
  }
  
  public function getGroupProcessor($group, $name)
  {
    if (array_key_exists($group, $this->processors)) {
      return $this->processors[$group]->get($name);
    } else {
      throw new Sabel_Exception_Runtime("group not found");
    }
  }
  
  public function addProcessorToGroup($group, $name, $processor)
  {
    $this->processors[$group]->add($name, $processor);
  }
  
  public function getProcessor($name)
  {
    return $this->processors[$name];
  }
  
  public function run($data = null)
  {
    if ($data !== null) {
      $through = $data;
    }
    
    if (count($this->bus) > 1) {
      foreach ($this->bus as $bus) {
        $bus->run($through);
      }
    }
    
    foreach ($this->processors as $name => $processor) {
      foreach ($this->listeners as $listener) {
        $listener->event($name, $processor, $this);
      }
      
      $processor->execute($this);
    }
    
    return $this->get("result");
  }
  
  public function addBus($name, $bus)
  {
    $this->bus[$name] = $bus;
  }
  
  public function set($key, $value)
  {
    $this->holder[$key] = $value;
  }
  
  public function get($key)
  {
    return $this->holder[$key];
  }
}
