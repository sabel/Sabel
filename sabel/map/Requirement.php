<?php


/**
 * Sabel_Map_Requirement
 * 
 * @package org.sabel
 * @author Mori Reo <mori.reo@gmail.com>
 */
class Sabel_Map_Requirement
{
  protected $rule;
  
  public function __construct($rule)
  {
    $this->rule = $rule;
  }
  
  public function isMatch($value)
  {
    $regex = '/' . $this->rule . '/';
    preg_match($regex, $value, $matchs);
    return (count($matchs) === 0) ? false : true;
  }
}