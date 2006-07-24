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
    return new Sabel_Controller_Map_Uri($this->entry['uri']);
  }
  
  public function getDestination()
  {
    if (isset($this->entry['destination'])) {
      return new Sabel_Controller_Map_Destination($this->entry['destination']);
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
    // @todo implement
  }
  
  public function hasRequirements()
  {
    return (isset($this->entry['requirements']));
  }
  
  public function hasOptions()
  {
    return (isset($this->entry['option']));
  }
}