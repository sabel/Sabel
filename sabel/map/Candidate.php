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
  
  const TYPE_KEY        = "TYPE";
  const REQUIREMENT_KEY = "REQUIREMENT";
  const OMITTABLE_KEY   = "OMITTABLE";
  const MATCH_ALL_KEY   = "MATCH_ALL";
  const VARIABLE_KEY    = "VARIABLE";
  
  const ELEMENT_NAME = "ELEMENT_NAME";
  
  protected $name = '';
  protected $elements = array();
  
  protected $size     = 0;
  protected $position = 0;
  
  public function __construct($name = '')
  {
    $this->setName($name);
  }
  
  public function setName($name)
  {
    $this->name = $name;
  }
  
  public function getName()
  {
    return $this->name;
  }
  
  public function getElementType()
  {
    $element = $this->getElement();
    return $element[self::TYPE_KEY];
  }
  
  public function equalsElementTypeWith($target)
  {
    return ($this->getElementType() === $target);
  }
  
  public function setElementVariable($value)
  {
    $element = $this->getElement();
    $this->elements[$element[self::ELEMENT_NAME]][self::VARIABLE_KEY] = $value;
  }
  
  public function getElementVariable()
  {
    $element = $this->getElement();
    if (isset($element[self::VARIABLE_KEY])) {
      return $element[self::VARIABLE_KEY];
    } else {
      return false;
    }
  }
  
  public function setElementVariableByName($name, $value)
  {
    if (isset($this->elements[$name])) {
      $this->elements[$name][self::VARIABLE_KEY] = $value;
    }
  }
  
  public function getElementVariableByName($name)
  {
    $result = false;
    if (isset($this->elements[$name])) {
      if (isset($this->elements[$name][self::VARIABLE_KEY])) {
        $result = $this->elements[$name][self::VARIABLE_KEY];
      }
    }
    return $result;
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
    if ($this->elements[$name][self::TYPE_KEY] === self::CONSTANT) {
      $msg = "could't apply requirement to constant elements";
      throw new Sabel_Map_Candidate_IllegalSetting($msg);
    }
    
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
  
  public function clearRequirement()
  {
    $element = $this->getElement();
    unset($element[self::REQUIREMENT_KEY]);
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
    if ($this->hasRequirement()) {
      $msg = "could't change to constant elements. it's has a requirement";
      throw new Sabel_Map_Candidate_IllegalSetting($msg);
    }
    
    if (isset($this->elements[$name])) {
      $this->elements[$name][self::TYPE_KEY] = self::CONSTANT;
    }
  }
  
  public function compareWithRequirement($value)
  {
    $requirement = $this->getRequirement();
    return ($requirement->isMatch($value));
  }
  
  public function setMatchAll($name, $bool)
  {
    if (isset($this->elements[$name])) {
      $this->elements[$name][self::MATCH_ALL_KEY] = $bool;
    }
  }
  
  public function setMatchAllByPosition($bool)
  {
    $element = $this->getElement();
    $element[self::MATCH_ALL_KEY] = $bool;
  }
  
  public function isMatchAll()
  {
    $element = $this->getElement();
    return (isset($element[self::MATCH_ALL_KEY]) &&
            $element[self::MATCH_ALL_KEY] === true);
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

class Sabel_Map_Candidate_IllegalSetting extends Exception {}