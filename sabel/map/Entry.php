<?php

/**
 * Map Entry class.
 *
 * @package org.sabel.map
 * @author Mori Reo <mori.reo@gmail.com>
 */
class Sabel_Map_Entry
{
  protected $name    = '';
  protected $request = null;
  
  protected $uri          = null;
  protected $destination  = null;
  protected $requirements = null;
  
  public function __construct($name)
  {
    $this->name = $name;
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
    }
  }
  
  public function setDestination($destination)
  {
    $this->destination = $destination;
  }
  
  public function getDestination()
  {
    $this->destination->mappingByRequest($this->getUri(), $this->getRequest());
    return $this->destination;
  }
  
  public function setRequirement($name, $rule)
  {
    $this->requirements->setRequirement($name, $rule);
  }
  
  public function getRequirements()
  {
    return $this->requirements;
  }
  
  public function setRequest($request)
  {
    $this->request = $request;
  }
  
  public function getRequest()
  {
    return $this->request;
  }
  
  public function uri($params)
  {
    $mapUri = $this->getUri();
    
    $buf = array();
    $cnt = 0;
    foreach ($mapUri as $name => $uri) {
      $name = $uri->getName();
      if (isset($params[$name])) {
        $buf[] = $params[$name];
      } else if ($uri->isConstant()) {
        $buf[] = $uri->getName();
      }
      
      ++$cnt;
    }
    
    $uri = join('/', $buf);
    return $uri;
  }
  
  public function isMatch()
  {
    $mapUri     = $this->getUri();
    $requestUri = $this->request->getUri();
    
    $reqs = $this->requirements;
    
    $match = true;
    for ($i = 0; $requestUri->count(); $i++) {
      $element = $mapUri->getElement($i);
      if (!is_object($element)) break;
      $request = $requestUri->get($i);
      
      if ($element->isConstant() && $request === $element->toString()) {
        $match = true;
        continue;
      } else if ($reqs->hasRequirementByName($element->getName())) {
        $req = $reqs->getByName($element->getName());
        $match = $req->isMatch($request);
        continue;
      } else {
        $match = false;
        break;
      }
    }
    
    return $match;
  }
}