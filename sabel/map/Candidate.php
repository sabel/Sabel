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
  const VARIABLE   = "variable";
  const CONSTANT   = "constant";
  const MODULE     = "module";
  const CONTROLLER = "controller";
  const ACTION     = "action";
  
  const MODULE_NAME     = "module_name";
  const CONTROLLER_NAME = "controller_name";
  const ACTION_NAME     = "action_name";
  
  const TYPE_KEY          = "type_key";
  const REQUIREMENT_KEY   = "requirement_key";
  const OMITTABLE_KEY     = "omittable_key";
  const MATCH_ALL_KEY     = "match_all_key";
  const VARIABLE_KEY      = "variable_key";
  const DEFAULT_VALUE_KEY = "default_value_key";
  const CACHE_KEY         = "cache_key";
  
  const ELEMENT_NAME = "element_name";
  
  protected $name     = "";
  protected $elements = array();
  
  protected
    $module     = "",
    $controller = "",
    $action     = "";
  
  protected $size     = 0;
  protected $position = 0;
  
  private $candidate = null;
  
  public function __construct($name = "")
  {
    $this->setName($name);
  }
  
  public function getDestination()
  {
    return new Sabel_Destination($this->module, $this->controller, $this->action);
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
  
  public function getElementVariables()
  {
    $results = array();
    $elements = $this->elements;
    foreach ($elements as $name => $variable) {
      if (isset($variable[self::VARIABLE_KEY])) {
        $results[$name] = $variable[self::VARIABLE_KEY];
      }
    }
    return $results;
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
  
  public function addElement($name, $type = null)
  {
    if ($type === null) {
      $type = self::VARIABLE;
    }
    
    $this->elements[$name][self::ELEMENT_NAME] = $name;
    $this->elements[$name][self::TYPE_KEY]     = $type;
  }
  
  public function getElement()
  {
    $elements = array_values($this->elements);
    return $elements[$this->position];
  }
  
  public function getElements()
  {
    return $this->elements;
  }
  
  public function getElementName()
  {
    $element = $this->getElement();
    return $element[self::ELEMENT_NAME];
  }
  
  public function getElementByName($name)
  {
    $result = (isset($this->elements[$name])) ? $this->elements[$name] : null;
    return ($result === false) ? null : $result;
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
  
  public function setCache($key)
  {
    $element = $this->getElement();
    $element[self::CACHE_KEY] = $key;
  }
  
  public function setDefaultValue($name, $value)
  {
    if (isset($this->elements[$name])) {
      if ($name === "action") {
        $this->setAction($value);
      }
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
  
  public function find($request)
  {
    $requests = $request->toArray();
    
    foreach (Sabel_Map_Configurator::getCandidates() as $candidate) {
      if ($this->matchToTokens($candidate, $requests)) {
        return $candidate;
      }
    }
    
    return null;
  }
  
  private final function matchToTokens($candidate, $requests)
  {
    $constantEstablished = false;
    foreach ($candidate as $element) {
      if ($constantEstablished) {
        if ($this->select(current($requests), $element)) {
          next($requests);
        }
      } else {
        if ($this->isConstantToken(current($requests), $element)) {
          $constantEstablished = true;
          if ($this->select(current($requests), $element)) {
            next($requests);
          }
        } elseif ($this->select(current($requests), $element)) {
          next($requests);
        } else {
          return false;
        }
      }
    }
    
    return true;
  }
  
  private final function select($token, $candidate)
  {
    $result = false;
    
    if (($token === false || $token === "") && $candidate->hasDefaultValue()) {
      $token = $candidate->getDefaultValue();
    }
    
    if ($candidate->isMatchAll()) {
      $result = true;
    } elseif (($token === false || $token === "") && $candidate->isOmittable()) {
      $result = true;
    } elseif ($candidate->hasRequirement()) {
      $result = $candidate->compareWithRequirement($token);
    } elseif ($candidate->isConstant() && $token !== $candidate->getElementName()) {
      return false;
    } else {
      $result =(boolean) $token;
    }
    
    // token value as a candidate variable
    if ($result) {
      if ($candidate->equalsElementTypeWith(Sabel_Map_Candidate::VARIABLE)) {
        $candidate->setElementVariable($token);
      } elseif ($candidate->equalsElementTypeWith(Sabel_Map_Candidate::MODULE)) {
        $candidate->setModule($token);
        $candidate->setElementVariable($token);
      } elseif ($candidate->equalsElementTypeWith(Sabel_Map_Candidate::CONTROLLER)) {
        $candidate->setController($token);
        $candidate->setElementVariable($token);
      } elseif ($candidate->equalsElementTypeWith(Sabel_Map_Candidate::ACTION)) {
        $candidate->setAction($token);
        $candidate->setElementVariable($token);
      }
    }
    
    return $result;
  }
  
  public function isConstantToken($token, $candidate)
  {
    return ($candidate->isConstant() && $token === $candidate->getElementName());
  }
  
  public function uri($parameters = null)
  {
    $candidate = null;
    
    if ($parameters === null) $parameters = array();
    
    foreach ($parameters as $key => $param) {
      switch ($key) {
        
        case "name":
        case "candidate":
          $candidate = Sabel_Map_Configurator::getCandidate($param);
          break;
        case "module":
        case "m":
          $parameters[":module"] = $param;
          unset($parameters[$key]);
          break;
        case "controller":
        case "c":
          $parameters[":controller"] = $param;
          unset($parameters[$key]);
          break;
        case "action":
        case "a":
          $parameters[":action"] = $param;
          unset($parameters[$key]);
          break;
      }
    }
    
    if ($candidate !== null) {
      $elements = $candidate->getElements();
    } else {
      $elements = $this->elements;
    }
    
    $buffer = array();
    
    $typeKey     = self::TYPE_KEY;
    $variableKey = self::VARIABLE_KEY;
    $elementName = self::ELEMENT_NAME;
    $module      = self::MODULE;
    $controller  = self::CONTROLLER;
    $action      = self::ACTION;
        
    foreach ($elements as $element) {
      switch ($element[$typeKey]) {
        case $module:
          if (isset($parameters[":module"])) {
            $buffer[] = $parameters[":module"];
          } else {
            $buffer[] = $element[$variableKey];
          }
          break;
        case $controller:
          if (isset($parameters[":controller"])) {
            $buffer[] = $parameters[":controller"];
          } else {
            $buffer[] = $element[$variableKey];
          }
          break;
        case $action:
          if (isset($parameters[":action"])) {
            $buffer[] = $parameters[":action"];
          } else {
            $buffer[] = $element[$variableKey];
          }
          break;
        case (array_key_exists($element[$elementName], $parameters)):
          $buffer[] = $parameters[$element[$elementName]];
          break;
        default:
          if (!isset($parameters[$element[$elementName]]) &&
               isset($element[self::OMITTABLE_KEY])) {
            // ignore
          } else {
            $buffer[] = $element[$elementName];
          }
          break;
      }
    }

    
    return join("/", $buffer);
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