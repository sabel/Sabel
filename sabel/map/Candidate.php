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
  const VARIABLE_MARK = ":";
  
  const VARIABLE   = "variable";
  const CONSTANT   = "constant";
  const TYPE_ARRAY = "array";
  
  const MODULE     = "module";
  const CONTROLLER = "controller";
  const ACTION     = "action";
  
  protected $name     = "";
  protected $elements = array();
  
  protected
    $destination = array("module" => "", "controller" => "", "action" => "");
    
  protected
    $size     = 0,
    $position = 0;
  
  private $candidate = null;
  
  public function __construct($name = "")
  {
    $this->name = $name;
  }
  
  public function route($uriRule)
  {
    $rules = explode("/", $uriRule);
    
    foreach ($rules as $element) {
      if (stripos($element, self::VARIABLE_MARK) === 0) {
        $name = ltrim($element, self::VARIABLE_MARK);
        switch ($name) {
          case self::MODULE:
          case self::CONTROLLER:
            $this->createElement($name);
            break;
          case ($this->hasExtension($name)):
            list($left, $right) = $this->diviedByExtension($name);
            if ($left === self::ACTION) {
              $element = new Sabel_Map_Element($left, self::ACTION);
            } else {
              $element = new Sabel_Map_Element($left, self::VARIABLE);
            }
            $element->extension = $right;
            $this->elements[$left] = $element;
            break;
          case self::ACTION:
            $this->createElement($name);
            break;
          case ($this->isArrayElement($name)):
            $name = str_replace("[]", "", $name);
            $this->createElement($name, self::TYPE_ARRAY);
            break;
          default:
            $this->createElement($name, self::VARIABLE);
            break;
        }
      } else {
        $this->createElement($element, self::CONSTANT);
      }
    }
    
    return $this;
  }
  
  private function isArrayElement($name)
  {
    return (strpos($name, "[]") !== false);
  }
  
  public function hasExtension($name)
  {
    $result = preg_match('/([a-zA-Z].*)[.]([a-zA-Z].*)/', $name);
    return ($result >= 1);
  }
  
  public function diviedByExtension($name)
  {
    $matches = array();
    preg_match('/([a-zA-Z].*)[.]([a-zA-Z].*)/', $name, $matches);
    array_shift($matches);
    return $matches;
  }
  
  public function setOptions(array $options)
  {
    if (isset($options["default"])) {
      foreach ($options["default"] as $key => $default) {
        $key = ltrim($key, self::VARIABLE_MARK);
        $this->setOmittable($key);
        $this->setDefaultValue($key, $default);
      }
    }
    
    if (isset($options["requirements"])) {
      foreach ($options["requirements"] as $key => $value) {
        $key = ltrim($key, self::VARIABLE_MARK);
        $this->setRequirement($key, new Sabel_Map_Requirement_Regex($value));
      }
    }
    
    if (isset($options["cache"])) {
      $this->setCache($options["cache"]);
    }
    
    $d =& $this->destination;
    if (isset($options["module"]))     $d["module"]     = $options["module"];
    if (isset($options["controller"])) $d["controller"] = $options["controller"];
    if (isset($options["action"]))     $d["action"]     = $options["action"];
  }
  
  public function getDestination()
  {
    return $this->destination;
  }
  
  public function setName($name)
  {
    $this->name = $name;
  }
  
  public function getName()
  {
    return $this->name;
  }
  
  public function createElement($name, $type = null)
  {
    if ($type === null) $type = $name;
    
    $element = new Sabel_Map_Element($name, $type);
    $this->elements[$name] = $element;
    return $element;
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
    return $element->name;
  }
  
  public function getElementByName($name)
  {
    $result = (isset($this->elements[$name])) ? $this->elements[$name] : null;
    return ($result === false) ? null : $result;
  }
  
  public function equalsElementTypeWith($target)
  {
    return ($this->getElementType() === $target);
  }
  
  public function setElementVariable($value)
  {
    $element = $this->getElement();
    $this->elements[$element->name]->variable = $value;
  }
  
  public function getElementVariables()
  {
    $results = array();
    $elements = $this->elements;
    
    foreach ($elements as $name => $element) {
      if ($element->hasVariable()) {
        $results[$name] = $element->variable;
      }
    }
    
    return $results;
  }
  
  public function getElementVariableByName($name)
  {
    $result = false;
    
    if (isset($this->elements[$name])) {
      $result = $this->elements[$name]->variable;
    }
    
    return $result;
  }
  
  public function setRequirement($name, $requirement)
  {
    if ($this->elements[$name]->isTypeOf(self::CONSTANT)) {
      $msg = "could't apply requirement to constant elements";
      throw new Sabel_Exception_Runtime($msg);
    }
    
    if (isset($this->elements[$name])) {
      $this->elements[$name]->requirement = $requirement;
    }
  }
  
  public function getRequirement()
  {
    $element = $this->getElement();
    return $element->requirement;
  }
  
  public function getRequirementByName($name)
  {
    if (isset($this->elements[$name]->requirement)) {
      return $this->elements[$name]->requirement;
    }
  }
  
  public function hasRequirement()
  {
    return $this->getElement()->hasRequirement();
  }
  
  public function clearRequirement()
  {
    $this->getElement()->requirement = null;
  }
  
  public function setOmittable($name)
  {
    if (isset($this->elements[$name])) {
      $this->elements[$name]->omittable = true;
    }
    
    return $this;
  }
  
  public function setOmittables($elements)
  {
    foreach ($elements as $name) {
      $this->setOmittable($name);
    }
    
    return $this;
  }
  
  public function isOmittable()
  {
    return $this->getElement()->omittable;
  }
  
  public function setCache($key)
  {
    $element = $this->getElement();
    $element->cache = $key;
  }
  
  public function setDefaultValue($name, $value)
  {
    if (isset($this->elements[$name])) {
      if ($name === "action") {
        $this->destination["action"] = $value;
      }
      
      $this->elements[$name]->default = $value;
    }
  }
  
  public function getDefaultValue()
  {
    if ($this->hasDefaultValue()) {
      return $this->getElement()->default;
    }
  }
  
  public function getDefaultValueByName($name)
  {
    if ($this->hasDefaultValueByName($name)) {
      return $this->elements[$name]->default;
    }
  }
  
  public function hasDefaultValue()
  {
    return $this->getElement()->default;
  }
  
  public function hasDefaultValueByName($name)
  {
    return $this->elements[$name]->hasDefault();
  }
  
  public function isConstant()
  {
    return $this->getElement()->isTypeOf(self::CONSTANT);
  }
  
  public function isConstantToken($urlElement, $element)
  {
    if ($element->isTypeOf(self::CONSTANT)) {
      return $element->equalsTo($urlElement);
    }
    
    return false;
  }
  
  public function setConstant($name)
  {
    if ($this->hasRequirement()) {
      $msg = "could't change to constant elements. it's has a requirement";
      throw new Sabel_Exception_Runtime($msg);
    }
    
    if (isset($this->elements[$name])) {
      $this->elements[$name]->type = self::CONSTANT;
    }
  }
  
  public function setMatchAll($name, $bool)
  {
    if (isset($this->elements[$name])) {
      $this->elements[$name]->matchAll = $bool;
    }
  }
  
  public function setMatchAllByPosition($bool)
  {
    $this->getElement()->matchAll = $bool;
  }
  
  public function isMatchAll()
  {
    return $this->getElement()->isMatchAll();
  }
  
  /**
   * evalute map rule between requested uri.
   *
   * @return boolean
   */
  public final function evalute(array $requests)
  {
    $constantEstablished = false;
    $extensionMatch = true;
    
    $uriElement = "";
    $elements = array_values($this->getElements());
    
    for ($i = 0; $i < count($elements); ++$i) {
      $element = $elements[$i];
      $uriElement = current($requests);
      
      if ($constantEstablished) {
        if (($uriElement = $this->compare($uriElement, $element)) !== false) {
          next($requests);
          $extensionMatch = $this->setVariableToElement($uriElement, $element);
        }
        continue;
      }
      
      if ($this->isConstantToken($uriElement, $element)) {
        $constantEstablished = true;
        if ($this->compare($uriElement, $element)) {
          next($requests);
          $extensionMatch = $this->setVariableToElement($uriElement, $element);
        }
      } elseif ($element->isTypeOf(self::TYPE_ARRAY)) {
        for ($rp = key($requests); $rp < count($requests); ++$rp) {
          $request = $requests[$rp];
          if ($this->hasExtension($request)) {
            if (isset($elements[$i + 1]) && $elements[$i + 1]->hasExtension()) {
              list(, $request_extension) = $this->diviedByExtension($request);
              if ($request_extension === $elements[$i + 1]->extension) {
                break;
              }
            }
          }
          
          $element->addVariable($request);
          next($requests);
        }
      } elseif (($uriElement = $this->compare($uriElement, $element)) !== false) {
        next($requests);
        $extensionMatch = $this->setVariableToElement($uriElement, $element);
      } else {
        return false;
      }
      
      if ($extensionMatch === false) return false;
    }
    
    return true;
  }
  
  private function compare($uriElement, $element)
  {
    $result = false;
    
    if (($uriElement === null || $uriElement === false) && $element->hasDefault()) {
      $uriElement = $element->default;
    }
    
    if ($element->isMatchAll()) {
      $result = $uriElement;
    } elseif (($uriElement === null || $uriElement === false) && $element->omittable) {
      $result = $uriElement;
    } elseif ($element->hasRequirement()) {
      $result = $element->compareWithRequirement($uriElement);
    } elseif ($element->isTypeOf(self::CONSTANT) && $uriElement !== $element->name) {
      $result = false;
    } else {
      $result = $uriElement;
    }
    
    return $result;
  }
  
  private function setVariableToElement($uriElement, $element)
  {
    switch ($element->type) {
      case self::VARIABLE:
        if ($this->hasExtension($uriElement)) {
          list($variable, $extension) = $this->diviedByExtension($uriElement);
          if ($element->extension !== "" && $element->extension !== $extension) return false;
          $element->extension = $extension;
        }
        $element->variable = $uriElement;
        break;
      case self::MODULE:
        $this->destination["module"] = $uriElement;
        $element->variable = $uriElement;
        break;
      case self::CONTROLLER:
        $this->destination["controller"] = $uriElement;
        $element->variable = $uriElement;
        break;
      case self::ACTION:
        if ($this->hasExtension($uriElement)) {
          list($variable, $extension) = $this->diviedByExtension($uriElement);
          if ($element->extension !== "" && $element->extension !== $extension) return false;
          $element->variable  = $variable;
          $element->extension = $extension;
          $this->destination["action"] = $variable;
        } else {
          $element->variable = $uriElement;
          $this->destination["action"] = $uriElement;
        }
        break;
      case self::TYPE_ARRAY:
        $element->variable = $uriElement;
        break;
    }
  }
  
  public function uri($parameters = null)
  {
    $candidate = null;
    
    if ($parameters === null) $parameters = array();
    
    foreach ($parameters as $key => $param) {
      switch ($key) {
        case "n":
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
    
    foreach ($elements as $element) {
      switch ($element->type) {
        case self::MODULE:
          if (isset($parameters[":module"])) {
            $buffer[] = $parameters[":module"];
          } else {
            $buffer[] = $element->variable;
          }
          break;
        case self::CONTROLLER:
          if (isset($parameters[":controller"])) {
            $buffer[] = $parameters[":controller"];
          } else {
            if ($element->hasVariable()) {
              $buffer[] = $element->variable;
            }
          }
          break;
        case self::ACTION:
          if (isset($parameters[":action"])) {
            $buffer[] = $parameters[":action"];
          } else {
            $buffer[] = $element->variable;
          }
          break;
        case (array_key_exists($element->name, $parameters)):
          $buffer[] = $parameters[$element->name];
          break;
        default:
          if (!isset($parameters[$element->name]) && $element->omittable) {
            // ignore
          } else {
            $buffer[] = $element->name;
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
