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
  protected $requestUri = null;
  
  public function __construct($name, $entry, $requestUri = null)
  {
    $this->name       = $name;
    $this->entry      = $entry;
    $this->requestUri = $requestUri;
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
  
  public function setRequestUri($uri)
  {
    $this->requestUri = $uri;
  }
  
  public function getRequestUri()
  {
    return $this->requestUri;
  }
}