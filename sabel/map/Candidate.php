<?php

/**
 * uri candidate
 *
 * @category   Map
 * @package    org.sabel.map
 * @author     Mori Reo <mori.reo@sabel.jp>
 * @copyright  2002-2006 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Map_Candidate
{ 
  const MODULE     = "module";
  const CONTROLLER = "controller";
  const ACTION     = "action";
  
  /**
   * @var string
   */
  protected $name = "";
  
  /**
   * @var Sabel_Map_Elements
   */
  protected $elements = null;
  
  /**
   * @var array
   */
  protected $destination = array("module" => "", "controller" => "", "action" => "");
  
  public function __construct($name)
  {
    $this->name = $name;
    $this->elements = new Sabel_Map_Elements();
  }
  
  public function route(Sabel_Map_Config_Route $route)
  {
    $options = array();
    $options["defaults"]     = $route->getDefaults();
    $options["requirements"] = $route->getRequirements();
    
    $this->destination = $route->createDestination();
    
    foreach (explode("/", $route->getUri()) as $name) {
      $this->elements->add(new Sabel_Map_Element($name, $options));
    }
    
    return $this;
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
  
  public function getDestination()
  {
    return new Sabel_Map_Destination($this->destination);
  }
  
  public function setName($name)
  {
    $this->name = $name;
  }
  
  public function getName()
  {
    return $this->name;
  }
  
  public function getElements()
  {
    return $this->elements->toArray();
  }
  
  public function getElementByName($name)
  {
    return $this->elements->getElement($name);
  }
  
  /**
   * evaluate map rule between requested uri.
   *
   * @return boolean
   */
  public final function evaluate(Sabel_Request $request)
  {
    $requests = $request->toArray();
    $elements = $this->elements;
    $elementsCount = $elements->count();
    
    if ($elementsCount > count($requests)) {
      $elements->appendToRequests($requests);
    }
    
    if ($elements->hasConstant() && $elements->matchToConstants($requests)) {
      for ($i = 0; $i < $elementsCount; ++$i) {
        $element = $elements->getElementAt($i);
        $partOfUri = (isset($requests[$i])) ? $requests[$i] : null;
        $this->setVariableToElement($partOfUri, $element);
      }
      
      return true;
    } elseif ($elements->hasArray() && count($requests) >= $elementsCount) {
      $last = $elementsCount - 1;
      $elements->getElementAt($last)->variable = array_slice($requests, $last);
      
      for ($i = 0; $i < $last; ++$i) {
        $partOfUri = (isset($requests[$i])) ? $requests[$i] : null;
        $this->setVariableToElement($partOfUri, $elements->getElementAt($i));
      }
      return true;
    } elseif ($elementsCount < count($requests)) {
      return false;
    }
    
    for ($i = 0; $i < $elementsCount; ++$i) {
      $element = $elements->getElementAt($i);
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
      case self::CONTROLLER:
        $this->destination[$element->type] = $partOfUri;
        $element->variable = $partOfUri;
        break;
      case self::ACTION:
        if ($this->hasExtension($partOfUri)) {
          list($variable, $extension) = $this->diviedByExtension($partOfUri);
          if ($element->extension !== "" && $element->extension !== $extension) return false;
          $element->variable  = $variable;
          $element->extension = $extension;
          $this->destination[self::ACTION] = $variable;
        } else {
          $element->variable = $partOfUri;
          $this->destination[self::ACTION] = $partOfUri;
        }
        break;
      case Sabel_Map_Element::TYPE_ARRAY:
        $element->variable = $partOfUri;
        break;
    }
  }
  
  public function uri($uriParameter = "")
  {
    if ($uriParameter === null) $uriParameter = "";
    
    if (!is_string($uriParameter)) {
      $message = "uri parameter must be a string.";
      throw new Sabel_Exception_InvalidArgument($message);
    }
    
    $parameters = array();
    if ($uriParameter !== "") {
      foreach (explode(",", $uriParameter) as $param) {
        list ($key, $val) = array_map("trim", explode(":", $param));
        if ($key === "n") $key = "candidate";
        $parameters[$key] = $val;
      }
    }
    
    $candidate = null;
    
    foreach ($parameters as $key => $param) {
      switch ($key) {
        case "n": case "name": case "candidate":
          $candidate = Sabel_Map_Configurator::getCandidateByName($param);
          break;
        case "m": case "module":
          $parameters[":module"] = $param;
          unset($parameters[$key]);
          break;
        case "c": case "controller":
          $parameters[":controller"] = $param;
          unset($parameters[$key]);
          break;
        case "a": case "action":
          $parameters[":action"] = $param;
          unset($parameters[$key]);
          break;
      }
    }
    
    if ($candidate !== null) {
      $elements = $candidate->getElements();
    } else {
      $elements = $this->elements->toArray();
    }
    
    $buffer = array();
    
    foreach ($elements as $element) {
      switch ($element->type) {
        case self::MODULE:
        case self::CONTROLLER:
        case self::ACTION:
          $index = ":" . $element->type;
          if (isset($parameters[$index])) {
            $buffer[] = $parameters[$index];
          } else {
            $buffer[] = $element->variable;
          }
          break;
        default:
          if (isset($parameters[$element->name])) {
            $buffer[] = $parameters[$element->name];
          } elseif (!$element->omittable) {
            $buffer[] = $element->name;
          }
          break;
      }
    }
    
    return implode("/", $buffer);
  }
}
