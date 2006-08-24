<?php

/**
 * Map Entry class.
 *
 * @package org.sabel.map
 * @author Mori Reo <mori.reo@gmail.com>
 */
class Sabel_Controller_Map_Entry
{
  protected $name       = '';
  protected $entry      = array();
  protected $request = null;
  
  public function __construct($name, $entry, $request = null)
  {
    $this->name       = $name;
    $this->entry      = $entry;
    
    if ($request) {
      $this->request = $request;
    } else {
      $this->request = new Sabel_Request_Request($this);
    }
  }
  
  public function getName()
  {
    return $this->name;
  }
  
  public function getUri()
  {
    return new Sabel_Controller_Map_Uri($this->entry['uri']);
  }
  
  public function getDestination()
  {
    return new Sabel_Controller_Map_Destination($this, $this->entry['destination']);
  }
  
  public function getRequirements()
  {
    if ($this->hasRequirements()) {
      $r = new Sabel_Map_Requirements($this->entry['requirements']);
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
    return (isset($this->entry['requirements']));
  }
  
  public function hasOptions()
  {
    return (isset($this->entry['option']));
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