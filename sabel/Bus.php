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
  
  /**
   * initialize bus data
   *
   * @param array $data
   * @return Sabel_Bus
   */
  public function init($data)
  {
    foreach ($data as $name => $value) {
      $this->set($name, $value);
    }
    return $this;
  }
  
  /**
   * add processor as listener.
   * 
   * @param string $name
   * @param Sabel_Bus_Processor $listener
   * @return Sabel_Bus
   */
  public function addProcessorAsListener($listener)
  {
    $this->listeners[$listener->name] = $listener;
    return $this;
  }
  
  /**
   * add processor to bus.
   *
   * @param string $name
   * @param Sabel_Bus_Processor $processor
   * @return Sabel_Bus
   */
  public function addProcessor(Sabel_Bus_Processor $processor)
  {
    $this->processors[$processor->name] = $processor;
    return $this;
  }
  
  public function addGroup(Sabel_Bus_Processor $processor = null)
  {
    $group = new Sabel_Bus_ProcessorGroup($processor->name);
    
    if ($processor !== null) {
      $group->add($processor);
    }
    
    $this->processors[$processor->name] = $group;
    
    return $this;
  }
  
  public function replaceGroup(Sabel_Bus_Processor $processor = null)
  {
    $group = new Sabel_Bus_ProcessorGroup();
    
    if ($processor !== null) {
      $group->add($processor->name, $processor);
    }
    
    unset($this->processors[$name]);
    $this->processors[$processor->name] = $group;
    
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
    
    if ($this->has("result")) {
      return $this->get("result");
    } else {
      return null;
    }
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
    if ($this->has($key)) {
      return $this->holder[$key];
    } else {
      return null;
    }
  }
  
  public function has($key)
  {
    return (array_key_exists($key, $this->holder));
  }
}
