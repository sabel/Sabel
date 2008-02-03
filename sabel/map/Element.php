<?php

/**
 * Sabel_Map_Element
 *
 * @category   Map
 * @package    org.sabel.map
 * @author     Mori Reo <mori.reo@sabel.jp>
 * @copyright  2002-2006 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Map_Element
{
  const VARIABLE_MARK = ":";
    
  const VARIABLE   = "variable";
  const CONSTANT   = "constant";
  const TYPE_ARRAY = "array";
  
  public $name = "";
  public $type = "";
  public $variable = "";

  public $extension = "";
  public $matchAll  = false;
  
  public $default     = "";
  public $omittable   = false;
  
  public $requirement = null;
  
  public $constant = false;
  public $isArray  = false;
  
  public function __construct($name, $options)
  {
    if (stripos($name, self::VARIABLE_MARK) === 0) {
      $name = ltrim($name, self::VARIABLE_MARK);
      if (strpos($name, "[]") !== false) {
        $this->isArray = true;
        $name = str_replace("[]", "", $name);
        $this->type = self::TYPE_ARRAY;
      } else {
        switch ($name) {
          case Sabel_Map_Candidate::MODULE:
          case Sabel_Map_Candidate::CONTROLLER:
          case Sabel_Map_Candidate::ACTION:
            $this->type = $name;
            break;
          default:
            $this->type = Sabel_Map_Element::VARIABLE;
            break;
        }
      }
    } else {
      $this->constant = true;
      $this->type = self::CONSTANT;
    }
    
    $this->name = $name;
    $this->setOptions($options);
  }
  
  public function equalsTo($name)
  {
    return ($this->name === $name);
  }
  
  public function isTypeOf($type)
  {
    return ($this->type === $type);
  }
  
  public function isArray()
  {
    return $this->isArray;
  }
  
  public function isConstant()
  {
    return $this->constant;
  }
  
  public function hasVariable()
  {
    return ($this->variable !== "");
  }
  
  public function hasRequirement()
  {
    return is_object($this->requirement);
  }
  
  public function isMatchAll()
  {
    return $this->matchAll;
  }
  
  public function hasDefault()
  {
    return ($this->default !== "");
  }
    
  public function compareWithRequirement($value)
  {
    return $this->requirement->isMatch($value);
  }
  
  public function hasExtension()
  {
    return ($this->extension !== "");
  }
  
  public function setOptions($options)
  {
    $key = self::VARIABLE_MARK . $this->name;
    if (array_key_exists($key, $options["defaults"])) {
      $this->default = $options["defaults"][$key];
      $this->omittable = true;
    }
    
    if (isset($options["requirements"][$key])) {
      $this->requirement = new Sabel_Map_Requirement_Regex($options["requirements"][$key]);
    }
  }
}
