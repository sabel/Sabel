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
        
        if ($this->isArrayElement($name)) {
          $name = str_replace("[]", "", $name);
          $this->createElement($name, Sabel_Map_Element::TYPE_ARRAY);
        } else {
          switch ($name) {
            case self::MODULE:
            case self::CONTROLLER:
              $this->createElement($name);
              break;
            case self::ACTION:
              $this->createElement($name);
              break;
            default:
              $this->createElement($name, Sabel_Map_Element::VARIABLE);
              break;
          }
        }
      } else {
        $this->createElement($element, Sabel_Map_Element::CONSTANT);
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
    return strpos($name, ".");
  }
  
  public function diviedByExtension($name)
  {
    $pos = strpos($name, ".");
    return array(substr($name, 0, $pos), substr($name, $pos + 1));
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
    
    if (isset($options["requirement"])) {
      foreach ($options["requirement"] as $key => $value) {
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
    if ($this->elements[$name]->isTypeOf(Sabel_Map_Element::CONSTANT)) {
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
    return $this->getElement()->isTypeOf(Sabel_Map_Element::CONSTANT);
  }
  
  public function isConstantToken($partOfUri, $element)
  {
    if ($element->isTypeOf(Sabel_Map_Element::CONSTANT)) {
      return $element->equalsTo($partOfUri);
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
      $this->elements[$name]->type = Sabel_Map_Element::CONSTANT;
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
   * evaluate map rule between requested uri.
   *
   * @return boolean
   */
  public final function evaluate(Sabel_Request $request)
  {
    $requests = $request->toArray();
    $elements = new Sabel_Map_Elements(array_values($this->elements));
    $elementsCount = $elements->count();
    
    if ($elementsCount > count($requests)) {
      $elements->appendToRequests($requests);
    }
    
    if ($elements->hasConstant() && $elements->matchToConstants($requests)) {
      for ($i = 0; $i < $elementsCount; ++$i) {
        $element = $elements->get($i);
        $partOfUri = (isset($requests[$i])) ? $requests[$i] : null;
        $this->setVariableToElement($partOfUri, $element);
      }
      
      return true;
    } elseif ($elements->hasArray()) {
      $last = $elementsCount - 1;
      $elements->get($last)->variable = array_slice($requests, $last);
      
      for ($i = 0; $i < $last; ++$i) {
        $partOfUri = (isset($requests[$i])) ? $requests[$i] : null;
        $this->setVariableToElement($partOfUri, $elements->get($i));
      }
      return true;
    } elseif ($elementsCount < count($requests)) {
      return false;
    }
    
    for ($i = 0; $i < $elementsCount; ++$i) {
      $element = $elements->get($i);
      $partOfUri = (isset($requests[$i])) ? $requests[$i] : null;
      if (($partOfUri = $this->compare($partOfUri, $element)) !== false) {
        $this->setVariableToElement($partOfUri, $element);
      } else {
        return false;
      }
    }
    
    return true;
  }
  
  private function compare($partOfUri, $element)
  {
    $result = false;
    
    if ($element->isMatchAll()) {
      $result = $partOfUri;
    } elseif ($element->hasRequirement()) {
      $result = ($element->compareWithRequirement($partOfUri)) ? $partOfUri : false;
    } elseif ($element->isConstant() && $partOfUri !== $element->name) {
      $result = false;
    } else {
      $result = $partOfUri;
    }
    
    return ($result === null) ? false : $result;
  }
  
  private function setVariableToElement($partOfUri, $element)
  {
    switch ($element->type) {
      case Sabel_Map_Element::VARIABLE:
        if ($this->hasExtension($partOfUri)) {
          list($variable, $extension) = $this->diviedByExtension($partOfUri);
          $element->variable  = $variable;
          $element->extension = $extension;
        } else {
          $element->variable = $partOfUri;
        }
        break;
      case self::MODULE:
        $this->destination["module"] = $partOfUri;
        $element->variable = $partOfUri;
        break;
      case self::CONTROLLER:
        $this->destination["controller"] = $partOfUri;
        $element->variable = $partOfUri;
        break;
      case self::ACTION:
        if ($this->hasExtension($partOfUri)) {
          list($variable, $extension) = $this->diviedByExtension($partOfUri);
          if ($element->extension !== "" && $element->extension !== $extension) return false;
          $element->variable  = $variable;
          $element->extension = $extension;
          $this->destination["action"] = $variable;
        } else {
          $element->variable = $partOfUri;
          $this->destination["action"] = $partOfUri;
        }
        break;
      case Sabel_Map_Element::TYPE_ARRAY:
        $element->variable = $partOfUri;
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
