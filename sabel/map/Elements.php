<?php

/**
 * Sabel_Map_Elements
 *
 * @category   Map
 * @package    org.sabel.map
 * @author     Mori Reo <mori.reo@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Map_Elements extends Sabel_Object
{
  /**
   * @var array
   */
  protected $components = array();
  
  /**
   * @var array
   */
  protected $namedComponents = array();
  
  public function add(Sabel_Map_Element $element)
  {
    $this->components[] = $element;
    $this->namedComponents[$element->name] = $element;
  }
  
  /**
   * @param string $name
   *
   * @return boolean
   */
  public function has($name)
  {
    return isset($this->namedComponents[$name]);
  }
  
  /**
   * @param int $index
   *
   * @return boolean
   */
  public function hasAt($index)
  {
    return isset($this->components[$index]);
  }
  
  /**
   * @param string $name
   *
   * @return Sabel_Map_Element
   */
  public function getElement($name)
  {
    if (isset($this->namedComponents[$name])) {
      return $this->namedComponents[$name];
    } else {
      return null;
    }
  }
  
  /**
   * @param int $index
   *
   * @return Sabel_Map_Element
   */
  public function getElementAt($index)
  {
    if (isset($this->components[$index])) {
      return $this->components[$index];
    } else {
      return null;
    }
  }
  
  /**
   * @return array
   */
  public function getNamedElements()
  {
    return $this->namedComponents;
  }
  
  /**
   * @return array
   */
  public function toArray()
  {
    return $this->components;
  }
  
  /**
   * @return boolean
   */
  public function hasDefaults()
  {
    foreach ($this->components as $element) {
      if ($element->hasDefault()) return true;
    }
    
    return false;
  }
  
  /**
   * @param array $requests
   *
   * @return void
   */
  public function appendToRequests(&$requests)
  {
    foreach ($this->components as $i => $element) {
      if ($element->hasDefault() && !isset($requests[$i])) {
        $requests[] = $element->default;
      }
    }
  }
  
  /**
   * @return boolean
   */
  public function hasConstant()
  {
    return $this->components[0]->isConstant();
  }
  
  /**
   * @return boolean
   */
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
  
  /**
   * @return boolean
   */
  public function hasArray()
  {
    return $this->components[$this->count() - 1]->isArray();
  }
  
  /**
   * @return int
   */
  public function count()
  {
    return count($this->components);
  }
}
