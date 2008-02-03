<?php

/**
 * Sabel_Map_Elements
 *
 * @category   Map
 * @package    org.sabel.map
 * @author     Mori Reo <mori.reo@sabel.jp>
 * @copyright  2002-2006 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Map_Elements extends Sabel_Object
{
  protected $components = array();
  protected $namedComponents = array();
  
  public function add(Sabel_Map_Element $element)
  {
    $this->components[] = $element;
    $this->namedComponents[$element->name] = $element;
  }
  
  public function has($name)
  {
    return (isset($this->namedComponents[$name]));
  }
  
  public function hasAt($index)
  {
    return (isset($this->components[$index]));
  }
  
  public function getElement($name)
  {
    if (isset($this->namedComponents[$name])) {
      return $this->namedComponents[$name];
    } else {
      return null;
    }
  }  
  
  public function getElementAt($index)
  {
    if (isset($this->components[$index])) {
      return $this->components[$index];
    } else {
      return null;
    }
  }
  
  public function getNamedElements()
  {
    return $this->namedComponents;
  }
  
  public function toArray()
  {
    return $this->components;
  }
  
  public function hasDefaults()
  {
    foreach ($this->components as $element) {
      if ($element->hasDefault()) return true;
    }
    return false;
  }
  
  public function appendToRequests(&$requests)
  {
    foreach ($this->components as $i => $element) {
      if ($element->hasDefault() && !isset($requests[$i])) {
        $requests[] = $element->default;
      }
    }
  }
  
  public function hasConstant()
  {
    return $this->components[0]->isConstant();
  }
  
  public function matchToConstants($requests)
  {
    for ($i = 0, $c = $this->count(); $i < $c; ++$i) {
      $element = $this->components[$i];
      if ($element->isConstant()) {
        if (!isset($requests[$i]) || !$element->equalsTo($requests[$i])) {
          return false;
        }
      }
    }
    
    return true;
  }
  
  public function getNext()
  {
    $next = key($this->components) + 1;
    
    if (isset($this->components[$next])) {
      return $this->components[$next];
    } else {
      return null;
    }
  }
  
  public function hasArray()
  {
    return $this->components[$this->count() - 1]->isArray();
  }
    
  public function count()
  {
    return count($this->components);
  }
}
