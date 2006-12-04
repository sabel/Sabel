<?php

/**
 * uri candidate
 *
 * @category   Map
 * @package    org.sabel.map
 * @author     Mori Reo <mori.reo@gmail.com>
 * @copyright  2002-2006 Mori Reo <mori.reo@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Map_Candidate implements Iterator
{
  const VARIABLE   = "VARIABLE";
  const CONSTANT   = "CONSTANT";
  
  const MODULE     = "MODULE";
  const CONTROLLER = "CONTROLLER";
  const ACTION     = "ACTION";
  
  const TYPE_KEY        = "TYPE_KEY";
  const REQUIREMENT_KEY = "REQUIREMENT";
  const OMITTABLE_KEY   = "OMITTABLE";
  
  const ELEMENT_NAME = "ELEMENT_NAME";
  
  protected $name = '';
  protected $elements = array();
  
  protected $size     = 0;
  protected $position = 0;
  
  public function setName($name)
  {
    $this->name = $name;
  }
  
  public function getName()
  {
    return $this->name;
  }
  
  public function addElement($name, $type = self::VARIABLE)
  {
    $this->elements[$name][self::ELEMENT_NAME] = $name;
    $this->elements[$name][self::TYPE_KEY] = $type;
  }
  
  public function getElement()
  {
    $elements = array_values($this->elements);
    return $elements[$this->position];
  }
  
  public function getElementName()
  {
    $element = $this->getElement();
    return $element[self::ELEMENT_NAME];
  }
  
  public function getElementByName($name)
  {
    return $this->elements[$name];
  }
  
  public function setRequirement($name, $requirement)
  {
    if (isset($this->elements[$name])) {
      $this->elements[$name][self::REQUIREMENT_KEY] = $requirement;
    }
  }
  
  public function getRequirement()
  {
    $element = $this->getElement();
    return $element[self::REQUIREMENT_KEY];
  }
  
  public function getRequirementByName($name)
  {
    if (isset($this->elements[$name])) {
      return $this->elements[$name][self::REQUIREMENT_KEY];
    }
  }
  
  public function hasRequirement()
  {
    $element = $this->getElement();
    return (isset($element[self::REQUIREMENT_KEY]));
  }
  
  public function setOmittable($name)
  {
    if (isset($this->elements[$name])) {
      $this->elements[$name][self::OMITTABLE_KEY] = true;
    }
  }
  
  public function isOmittable()
  {
    $element = $this->getElement();
    return (isset($element[self::OMITTABLE_KEY]));
  }
  
  public function isConstant()
  {
    $element = $this->getElement();
    return ($element[self::TYPE_KEY] === self::CONSTANT);
  }
  
  public function setConstant($name)
  {
    if (isset($this->elements[$name])) {
      $this->elements[$name][self::TYPE_KEY] = self::CONSTANT;
    }
  }
  
  public function compareWithRequirement($value)
  {
    $requirement = $this->getRequirement();
    return ($requirement->isMatch($value));
  }
  
  public function current()
  {
    return $this;
  }
  
  public function key()
  {
    return $this->position;
  }
  
  public function valid()
  {
    return ($this->position < $this->size);
  }
  
  public function next()
  {
    $this->position++;
  }
  
  public function rewind()
  {
    $this->position = 0;
    $this->size = count($this->elements);
  }
}