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
  protected $requestUri;
  
  public function __construct($entry, $requestUri)
  {
    $this->entry = $entry;
    $this->requestUri = $requestUri;
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
      $uri = new Sabel_Controller_Map_Uri($this->entry['uri']);
      $destination = array();
      foreach ($uri->getElements() as $element) {
        switch ($element->isReservedWord()) {
          case ($element->isModule()):
            $destination['module'] = $this->requestUri->get(0);
            break;
          case ($element->isController()):
            $destination['controller'] = $this->requestUri->get(1);
            break;
          case ($element->isAction()):
            $destination['action'] = $this->requestUri->get(2);
            break;
        }
      }
      
      return new Sabel_Controller_Map_Destination($destination);
    }
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
}