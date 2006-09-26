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
  
  protected $uri = null;
  protected $destination = null;
  protected $requirements = null;
  
  public function __construct($name, $rawEntry = null)
  {
    $this->name = $name;
    
    if (!is_null($rawEntry)) {
      $this->rawEntry = $rawEntry;
    }
    
    $this->requirements = new Sabel_Map_Requirements();
  }
  
  public function getName()
  {
    return $this->name;
  }
  
  public function setUri($uri)
  {
    $this->uri = $uri;
  }
  
  public function getUri()
  {
    if (is_object($this->uri)) {
      return $this->uri;
    } else if (is_string($this->uri)) {
      return new Sabel_Map_Uri($this->uri);
    } else {
      return new Sabel_Map_Uri($this->rawEntry['uri']);
    }
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
  
  public function setRequirement($name, $rule)
  {
    $this->requirements->setRequirement($name, $rule);
  }
  
  public function getRequirements()
  {
    return $this->requirements;
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