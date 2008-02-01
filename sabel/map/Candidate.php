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
        case "n": case "name": case "candidate":
          $candidate = Sabel_Context::getContext()->getCandidatesByName($param);
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
          if (isset($parameters[$element->name]) || !$element->omittable) {
            $buffer[] = $element->name;
          }
          break;
      }
    }
    
    return implode("/", $buffer);
  }
}
