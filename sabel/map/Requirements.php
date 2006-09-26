<?php

/**
 * Sabel_Map_Requirements
 * 
 * @package org.sabel
 * @author Mori Reo <mori.reo@gmail.com>
 */
class Sabel_Map_Requirements
{
  protected $requirements = array();
  
  public function __construct()
  {
  }
  
  public function setRequirement($name, $rule)
  {
    $this->requirements[$name] = new Sabel_Map_Requirement($rule);
  }
  
  public function getByName($name)
  {
    return $this->requirements[$name];
  }
}