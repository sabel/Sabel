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
  
  private $callbacks = array();
  
  private $list = null;
  
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
   * add processor as listener.
   * 
   * @param string $name
   * @param Sabel_Bus_Processor $listener
   * @return Sabel_Bus
   */
  public function addProcessorAsListener($processor)
  {
    $this->listeners[$processor->name] = $processor;
    if ($this->list === null) {
      $this->list = new Sabel_Bus_ProcessorList($processor);
    } else {
      $this->list->insertNext($processor);
      $this->list = $this->list->getLast();
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
      $this->list = new Sabel_Bus_ProcessorList($processor);
    } else {
      $this->list->insertNext($processor);
      $this->list = $this->list->getLast();
    }
    
    return $this;
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
    $callbacks = array();
    
    while ($processorList !== null) {
      $processor = $processorList->get();
      $result = $processor->execute($this);
      
      if (isset($callbacks[$processor->name])) {
        if ($result === true) {
          $callback = $callbacks[$processor->name];
          if (is_array($callback)) {
            foreach ($callback as $c) {
              $result = $c->processor->{$c->method}($this);
              foreach ($this->listeners as $listener) {
                $listener->event($this, $c->processor, $c->method, $result);
              }
            }
          } else {
            $result = $callback->processor->{$callback->method}($this);
            foreach ($this->listeners as $listener) {
              $listener->event($this, $callback->processor, $callback->method, $result);
            }
          }

        }
      }
      
      if ($result instanceof Sabel_Bus_ProcessorCallback) {
        $callbacks[$result->when][] = $result;
      }
      
      $processorList = $processorList->next();
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
