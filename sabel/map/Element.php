<?php

class Sabel_Map_Element
{
  public $name = "";
  public $type = "";
  
  public $default = "";
  public $variable  = "";
  
  public $cache = "";
  public $omittable = false;
  public $matchAll = false;
  public $requirement = null;
  
  public function __construct($name, $type)
  {
    $this->name = $name;
    $this->type = $type;
  }
  
  public function equalsTo($name)
  {
    return ($this->name === $name);
  }
  
  public function isTypeOf($type)
  {
    return ($this->type === $type);
  }
  
  public function hasVariable()
  {
    return ($this->variable !== "");
  }
  
  public function hasRequirement()
  {
    return (is_object($this->requirement));
  }
  
  public function isMatchAll()
  {
    return $this->matchAll;
  }
  
  public function hasDefault()
  {
    return ($this->default !== "");
  }
  
  public function addVariable($variable)
  {
    if ($this->variable === "") $this->variable = array();
    $this->variable[] = $variable;
  }
  
  public function compareWithRequirement($value)
  {
    $this->requirement->isMatch($value);
  }
}