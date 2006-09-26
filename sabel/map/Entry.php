<?php

/**
 * Map Entry class.
 *
 * @package org.sabel.map
 * @author Mori Reo <mori.reo@gmail.com>
 */
class Sabel_Map_Entry
{
  protected $name     = '';
  protected $rawEntry = array();
  protected $request  = null;
  
  protected $destination = null;
  
  public function __construct($name, $rawEntry = null)
  {
    $this->name     = $name;
    
    if (!is_null($rawEntry)) {
      $this->rawEntry = $rawEntry;
    }
  }
  
  public function getName()
  {
    return $this->name;
  }
  
  public function getUri()
  {
    return new Sabel_Map_Uri($this->rawEntry['uri']);
  }
  
  public function setDestination($destination)
  {
    $this->destination = $destination;
  }
  
  public function getDestination()
  {
    $dest = new Sabel_Map_Destination();
    
    $destCfg = $this->rawEntry['destination'];
    $dest->setModule($destCfg['module']);
    $dest->setController($destCfg['controller']);
    $dest->setAction($destCfg['action']);
    unset($destCfg);
    
    // @todo rename.
    $dest->mappingByRequest($this->getUri(), $this->getRequest());
    
    return $dest;
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
  
  public function isMatch()
  {
  }
}