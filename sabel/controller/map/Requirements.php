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
  
  public function __construct($requirements)
  {
    $this->requirements = $requirements;
  }
  
  public function getRequirements()
  {
    $requirements = array();
    foreach ($this->requirements as $requirement) {
      $requirements[] = new Sabel_Map_Requirement($requirement);
    }
    return $requirements;
  }
}

/**
 * Sabel_Map_Requirement
 * 
 * @package org.sabel
 * @author Mori Reo <mori.reo@gmail.com>
 */
class Sabel_Map_Requirement
{
  protected $requirement;
  
  public function __construct($requirement)
  {
    $this->requirement = $requirement;
  }
  
  public function isMatch($value)
  {
    $regex = '/' . $this->requirement . '/';
    preg_match($regex, $value, $matchs);
    return (count($matchs) === 0) ? false : true;
  }
}

?>