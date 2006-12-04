<?php

class Sabel_Map_Requirement_Regex implements Sabel_Map_Requirement_Interface
{
  protected $regex = '';
  
  public function __construct($regex)
  {
    $this->regex = $regex;
  }
  
  public function isMatch($value)
  {
    $match = preg_match($this->regex, $value, $matches);
    return (boolean) $match;
  }
}