<?php

/**
 * Map Entry class.
 *
 * @package org.sabel.map
 * @author Mori Reo <mori.reo@gmail.com>
 */
class Sabel_Controller_Map_Entry
{
  protected $entry;
  
  public function __construct($entry)
  {
    $this->entry = $entry;
  }
  
  public function getUri()
  {
    return $this->entry['uri'];
  }
  
  public function countUri()
  {
    return count(explode('/', $this->getUri()));
  }
  
  public function getDestination()
  {
    if (isset($this->entry['destination'])) {
      return $this->entry['destination'];
    } else {
      return null;
    }
  }
  
  public function getRequirements()
  {
    if ($this->hasRequirements()) {
      return $this->entry['requirements'];
    } else {
      return null;
    }
  }
  
  public function validate()
  {
    
  }
  
  public function hasRequirements()
  {
    if (isset($this->entry['requirements'])) {
      return true;
    } else {
      return false;
    }
  }
  
  public function hasOptions()
  {
    if (isset($this->entry['option'])) {
      return true;
    } else {
      return false;
    }
  }
}

?>