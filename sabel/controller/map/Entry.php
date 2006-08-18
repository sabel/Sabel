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
  
  public function __construct($name, $entry, $requestUri)
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
    if (isset($this->entry['destination'])) {
      // @todo implements when specified reserved word.
      return new Sabel_Controller_Map_Destination($this, $this->entry['destination']);
    } else {
      $statuses = array('module'=>false, 'controller'=>false, 'action'=>false);
      
      $uri = new Sabel_Controller_Map_Uri($this->entry['uri']);
      $ruri = $this->requestUri;
      $destination = array();
      foreach ($uri->getElements() as $element) {
        if ($element->isModule()) {
          $statuses['module'] = true;
          $destination['module'] =  ($ruri->has(0))
                                   ? $ruri->get(0)
                                   : Sabel_Controller_Map::getDefaultModule();
        } else if ($element->isController()) {
          $statuses['controller'] = true;
          $destination['controller'] =  ($ruri->has(1))
                                       ? $ruri->get(1)
                                       : Sabel_Controller_Map::getDefaultController();
        } else if ($element->isAction()) {
          $statuses['action'] = true;
          $destination['action'] =  ($ruri->has(2))
                                   ? $ruri->get(2)
                                   : Sabel_Controller_Map::getDefaultAction();
        }
      }
      
      return new Sabel_Controller_Map_Destination($this, $destination);
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
  
  public function getRequestUri()
  {
    return $this->requestUri;
  }
}