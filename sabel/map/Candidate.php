<?php

Sabel::using('Sabel_Map_Selecter_Impl');

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
  const VARIABLE   = 0x01;
  const CONSTANT   = 0x02;
  const MODULE     = 0x03;
  const CONTROLLER = 0x04;
  const ACTION     = 0x05;
  
  const MODULE_NAME     = 0x40;
  const CONTROLLER_NAME = 0x41;
  const ACTION_NAME     = 0x42;
  
  const TYPE_KEY          = 0x50;
  const REQUIREMENT_KEY   = 0x51;
  const OMITTABLE_KEY     = 0x52;
  const MATCH_ALL_KEY     = 0x53;
  const VARIABLE_KEY      = 0x54;
  const DEFAULT_VALUE_KEY = 0x55;
  
  const ELEMENT_NAME = "ELEMENT_NAME";
  
  protected $name     = '';
  protected $elements = array();
  
  protected
    $module     = '',
    $controller = '',
    $action     = '';
  
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
    if (isset($this->elements[$name][self::VARIABLE_KEY])) {
      $result = $this->elements[$name][self::VARIABLE_KEY];
    }
    return $result;
  }
  
  public function hasElementVariableByName($name)
  {
    return (isset($this->elements[$name][self::VARIABLE_KEY]));
  }
  
  public function addElement($name, $type = self::VARIABLE)
  {
    $this->elements[$name][self::ELEMENT_NAME] = $name;
    $this->elements[$name][self::TYPE_KEY]     = $type;
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
    return (isset($this->elements[$name])) ?$this->elements[$name] : null;
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
    if (isset($this->elements[$name][self::REQUIREMENT_KEY])) {
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
  
  public function setDefaultValue($name, $value)
  {
    if (isset($this->elements[$name])) {
      $this->elements[$name][self::DEFAULT_VALUE_KEY] = $value;
    }
  }
  
  public function getDefaultValue()
  {
    if ($this->hasDefaultValue()) {
      $element = $this->getElement();
      return $element[self::DEFAULT_VALUE_KEY];
    }
  }
  
  public function getDefaultValueByName($name)
  {
    if ($this->hasDefaultValueByName($name)) {
      return $this->elements[$name][self::DEFAULT_VALUE_KEY];
    }
  }
  
  public function hasDefaultValue()
  {
    $element = $this->getElement();
    return (isset($element[self::DEFAULT_VALUE_KEY]));
  }
  
  public function hasDefaultValueByName($name)
  {
    return (isset($this->elements[$name][self::DEFAULT_VALUE_KEY]));
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
  
  public function setModule($module)
  {
    $this->module = $module;
  }
  
  public function getModule()
  {
    return $this->module;
  }
  
  public function hasModule()
  {
    return ($this->module !== "");
  }
  
  public function setController($controller)
  {
    $this->controller = $controller;
  }
  
  public function getController()
  {
    return $this->controller;
  }
  
  public function hasController()
  {
    return ($this->controller !== "");
  }
  
  public function setAction($action)
  {
    $this->action = $action;
  }
  
  public function getAction()
  {
    return $this->action;
  }
  
  public function hasAction()
  {
    return ($this->action !== "");
  }
  
  public function find($tokens)
  {
    foreach (Sabel_Map_Configurator::getCandidates() as $candidate) {
      if ($this->matchToTokens($candidate, $tokens)) return $candidate;
    }
    
    throw new Sabel_Map_Candidate_NotFound("check your config/map.php");
  }
  
  protected function matchToTokens($candidate, $tokens)
  {
    $tokens = clone $tokens;
    $selecter = new Sabel_Map_Selecter_Impl();
    
    foreach ($candidate as $element) {
      if ($selecter->select($tokens->current(), $element)) {
        $tokens->next();
      } else {
        return false;
      }
    }
    
    return true;
  }
  
  public function uri($parameters = null)
  {
    if ($parameters === null) $parameters = array();
    
    foreach ($parameters as $key => $param) {
      switch ($key) {
        case 'module':
        case 'm':
          $parameters[':module'] = $param;
          unset($parameters[$key]);
          break;
        case 'controller':
        case 'c':
          $parameters[':controller'] = $param;
          unset($parameters[$key]);
          break;
        case 'action':
        case 'a':
          $parameters[':action'] = $param;
          unset($parameters[$key]);
          break;
      }
    }
    
    $elements = $this->elements;
    $buffer = array();
    
    foreach ($elements as $element) {
      if ($element[self::TYPE_KEY] === self::MODULE) {
        if (isset($parameters[':module'])) {
          $buffer[] = $parameters[':module'];
        } else {
          $buffer[] = $element[self::VARIABLE_KEY];
        }
      } elseif ($element[self::TYPE_KEY] === self::CONTROLLER) {
        if (isset($parameters[':controller'])) {
          $buffer[] = $parameters[':controller'];
        } else {
          $buffer[] = $element[self::VARIABLE_KEY];
        }
      } elseif ($element[self::TYPE_KEY] === self::ACTION) {
        if (isset($parameters[':action'])) {
          $buffer[] = $parameters[':action'];
        } else {
          $buffer[] = $element[self::VARIABLE_KEY];
        }
      } elseif (isset($parameters[$element[self::ELEMENT_NAME]])) {
        $buffer[] = $parameters[$element[self::ELEMENT_NAME]];
      }
    }
    
    return join('/', $buffer);
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
class Sabel_Map_Candidate_NotFound extends Exception {}