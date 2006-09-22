<?php

/**
 * Map Entry class.
 *
 * @package org.sabel.map
 * @author Mori Reo <mori.reo@gmail.com>
 */
class Sabel_Controller_Map_Entry
{
  protected $name     = '';
  protected $rawEntry = array();
  protected $request  = null;
  
  public function __construct($name, $rawEntry)
  {
    $this->name     = $name;
    $this->rawEntry = $rawEntry;
  }
  
  public function getName()
  {
    return $this->name;
  }
  
  public function getUri()
  {
    return new Sabel_Controller_Map_Uri($this->rawEntry['uri']);
  }
  
  public function getDestination()
  {
    return new Sabel_Controller_Map_Destination($this, $this->rawEntry['destination']);
  }
  
  public function getRequirements()
  {
    if ($this->hasRequirements()) {
      $r = new Sabel_Map_Requirements($this->rawEntry['requirements']);
      return $r->getRequirements();
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
    return (isset($this->rawEntry['requirements']));
  }
  
  public function hasOptions()
  {
    return (isset($this->rawEntry['option']));
  }
  
  public function setRequest($request)
  {
    $this->request = $request;
  }
  
  public function getRequest()
  {
    return $this->request;
  }
}