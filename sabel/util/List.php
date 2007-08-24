<?php

/**
 * Sabel_Util_List
 *
 * @category   Util
 * @package    org.sabel.util
 * @author     Mori Reo <mori.reo@gmail.com>
 * @copyright  2002-2006 Mori Reo <mori.reo@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Util_List extends Sabel_Object
{
  public $name     = "";
  
  public $previous = null;
  public $current  = null;
  public $next     = null;
  
  public function __construct($name, $current = null)
  {
    $this->name    = $name;
    $this->current = $current;
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
  
  public function size()
  {
    $list = $this->getFirst();
    $size = 1;
    
    while ($list->hasNext()) {
      $size++;
      $list = $list->next;
    }
    
    return $size;
  }
  
  public function insertPrevious($name, $object)
  {
    $previous = new self($name, $object);
    $previous->setNext($this);
    
    if ($this->isFirst()) {
      $previous->setPrevious(null);
    } else {
      $this->previous->setNext($previous);
      $previous->setPrevious($this->previous);
    }
    
    $this->setPrevious($previous);
    
    return $this;
  }
  
  public function insertNext($name, $object)
  {
    $next = new self($name, $object);
    
    $next->setPrevious($this);
    
    if ($this->isLast()) {
      $next->setNext(null);
    } else {
      $next->setNext($this->next);
    }
    
    $this->setNext($next);
    
    return $next;
  }
  
  public function find($name)
  {
    $list = $this->getFirst();
    while ($list !== null) {
      if ($name === $list->name) {
        return $list;
      }
      $next = $list->next();
      unset($list);
      $list = $next;
    }
  }
  
  public function replace($object)
  {
    unset($this->current);
    $this->current = $object;
  }
  
  public function add($object)
  {
    $this->next = $object;
  }
  
  public function setNext($object)
  {
    $this->next = $object;
  }
  
  public function unlinkNext()
  {
    $this->next = null;
  }
  
  public function setPrevious($object)
  {
    $this->previous = $object;
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
  
  public function unsetNext()
  {
    if ($this->next !== null) {
      $this->next->unsetNext();
      unset($this->current);
    }
  }
}
