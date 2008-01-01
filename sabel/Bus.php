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
class Sabel_Bus extends Sabel_Object
{
  private
    $bus        = array(),
    $holder     = array(),
    $processors = array(),
    $list       = null;
    
  private
    $beforeEvent = array(),
    $afterEvent  = array();
    
  public function __construct()
  {
    Sabel_Context::getContext()->setBus($this);
  }
  
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
   * add processor to bus.
   *
   * @param string $name
   * @param Sabel_Bus_Processor $processor
   * @return Sabel_Bus
   */
  public function addProcessor(Sabel_Bus_Processor $processor)
  {
    if ($this->list === null) {
      $this->list = new Sabel_Util_List($processor->name, $processor);
    } else {
      $this->list->insertNext($processor->name, $processor);
      $this->list = $this->list->getLast();
    }
    
    return $this;
  }
  
  public function getProcessor($name)
  {
    return $this->list->find($name)->get();
  }
  
  public function getList()
  {
    return $this->list;
  }
  
  public function run()
  {
    $processorList = $this->list->getFirst();
    
    while ($processorList !== null) {
      $s = microtime();
      $processor = $processorList->get();
      $processor->setBus($this);
      $this->beforeEvent($processor->name);
      $result = $processor->execute($this);
      $this->afterEvent($processor->name);
      
      if (ENVIRONMENT !== PRODUCTION) {
        $time = (microtime() - $s);
        l("execute " . $processor->name . " (time: {$time})", LOG_DEBUG);
      }
      
      $processorList = $processorList->next();
    }
    
    $processorList = $this->list->getFirst();
    while ($processorList !== null) {
      $processor = $processorList->get();
      if ($processor->hasMethod("shutdown")) {
        l("shutdown " . $processor->name, LOG_DEBUG);
        $processor->shutdown($this);
      }
      
      $processorList = $processorList->next();
    }
    
    return ($this->has("result")) ? $this->get("result") : null;
  }
  
  public function attachExecuteBeforeEvent($processorName, $object, $method)
  {
    $this->attachEvent($processorName, $object, $method, "before");
  }
  
  public function attachExecuteAfterEvent($processorName, $object, $method)
  {
    $this->attachEvent($processorName, $object, $method, "after");
  }
  
  public function attachExecuteEvent($processorName, $object, $method)
  {
    $this->attachExecuteAfterEvent($processorName, $object, $method);
  }
  
  private function attachEvent($processorName, $object, $method, $when)
  {
    $evt = new stdClass();
    $evt->object = $object;
    $evt->method = $method;
    
    $var = $when . "Event";
    $events =& $this->$var;
    if (isset($events[$processorName])) {
      $events[$processorName][] = $evt;
    } else {
      $events[$processorName] = array($evt);
    }
  }
  
  private function beforeEvent($processorName)
  {
    if (isset($this->beforeEvent[$processorName])) {
      foreach ($this->beforeEvent[$processorName] as $event) {
        $event->object->{$event->method}($this);
      }
    }
  }
  
  private function afterEvent($processorName)
  {
    if (isset($this->afterEvent[$processorName])) {
      foreach ($this->afterEvent[$processorName] as $event) {
        $event->object->{$event->method}($this);
      }
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
  
  /**
   * check bus has a data
   * 
   * @param mixed string or array
   * @return bool
   */
  public function has($key)
  {
    if (is_array($key)) {
      $result = true;
      foreach ($key as $k) {
        $result = $this->has($k);
        if (!$result) return false;
      }
      return $result;
    } else {
      return (array_key_exists($key, $this->holder));
    }
  }
  
  public function &getHolder()
  {
    return $this->holder;
  }
}
