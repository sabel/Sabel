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
  private $bus        = array();
  private $holder     = array();
  private $processors = array();
  private $callbacks  = array();
  private $list       = null;
  
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
  
  public function run($data = null)
  {
    if ($data !== null) {
      $through = $data;
    }
    
    $processorList = $this->list->getFirst();
    
    while ($processorList !== null) {
      $processor = $processorList->get();
      l("[bus] execute " . $processor->name, LOG_DEBUG);
      $processor->setBus($this);
      $result = $processor->execute($this);
      $this->addonEvent($processor);
      $this->callback($processor);
      
      if ($result instanceof Sabel_Bus_ProcessorCallback) {
        $this->callbacks[$result->when][] = $result;
      }
      
      $processorList = $processorList->next();
    }
    
    $processorList = $this->list->getFirst();
    while ($processorList !== null) {
      $processor = $processorList->get();
      l("[bus] shutdown: " . $processor->name, LOG_DEBUG);
      if ($processor->hasMethod("shutdown")) {
        $processor->shutdown($this);
      }
      
      $processorList = $processorList->next();
    }
    
    return ($this->has("result")) ? $this->get("result") : null;
  }
  
  private $addonEvent = array();
  public function attachExecuteEvent($processorName, $addon, $method)
  {
    $o = new StdClass();
    $o->addon = $addon;
    $o->method = $method;
    $this->addonEvent[$processorName] = $o;
  }
  private function addonEvent($processor)
  {
    if (isset($this->addonEvent[$processor->name])) {
      $o = $this->addonEvent[$processor->name];
      $o->addon->{$o->method}($this);
    }
  }
  
  public function callback($processor)
  {
    if (isset($this->callbacks[$processor->name])) {
      $callback = $this->callbacks[$processor->name];
      if (!is_array($callback)) $callback = array($callback);
      
      foreach ($callback as $c) {
        $method = $c->method;
        $c->processor->$method($this);
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
