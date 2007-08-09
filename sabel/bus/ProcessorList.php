<?php

/**
 * Sabel_Bus_ProcessorList
 *
 * @category   Bus
 * @package    org.sabel.bus
 * @author     Mori Reo <mori.reo@gmail.com>
 * @copyright  2002-2006 Mori Reo <mori.reo@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Bus_ProcessorList
{
  private $name     = "";
  private $previous = null;
  private $current  = null;
  private $next     = null;
  
  private $listeners = array();
  
  public function __construct($name, $processor)
  {
    $this->name = $name;
    $this->current = $processor;
  }
  
  public function get()
  {
    return $this->current;
  }
  
  public function getFirst()
  {
    $buf = $this;
    
    while (!$buf->isFirst()) {
      $buf = $buf->previous();
    }
    
    return $buf;
  }
  
  public function getLast()
  {
    $buf = $this;
    
    while (!$buf->isLast()) {
      $buf = $buf->next();
    }
    
    return $buf;
  }
  
  public function insertPrevious($name, $processor)
  {
    $previous = new self($name, $processor);
    
    $previous->setNext($this);
    
    
    if ($this->isFirst()) {
      $previous->setPrevious(null);
    } else {
      $previous->setPrevious($this->previous);
    }
    
    $this->setPrevious($previous);
    
    $this->notify($name, $previous);
    
    return $this;
  }
  
  public function insertNext($name, $processor)
  {
    $next = new self($name, $processor);
    
    $next->setPrevious($this);
    
    if ($this->isLast()) {
      $next->setNext(null);
    } else {
      $next->setNext($this->next);
    }
    
    $this->setNext($next);
    $this->notify($name, $next);
    return $this;
  }
  
  public function notify($name, $processor)
  {
    foreach ($this->listeners as $listener) {
      $listener->update($name, $processor);
    }
  }
  
  public function addNext($processor)
  {
    $this->next = $processor;
  }
  
  public function setNext($processor)
  {
    $this->next = $processor;
  }
  
  public function setPrevious($processor)
  {
    $this->previous = $processor;
  }
  
  public function isFirst()
  {
    return ($this->previous === null);
  }
  
  public function isLast()
  {
    return ($this->next === null);
  }
  
  public function previous()
  {
    return $this->previous;
  }
  
  public function next()
  {
    return $this->next;
  }
  
  public function hasNext()
  {
    return ($this->next !== null);
  }
  
  public function addListener($listener)
  {
    $this->listeners[] = $listener;
  }
}
