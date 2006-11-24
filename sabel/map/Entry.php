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
    } elseif (is_string($this->uri)) {
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
    if (!is_array($params)) $params = array($params);
    foreach ($params as $key => $param) {
      switch ($key) {
        case 'm':
          $params['module'] = $param;
          unset($params[$key]);
          break;
        case 'c':
          $params['controller'] = $param;
          unset($params[$key]);
          break;
        case 'a':
          $params['action'] = $param;
          unset($params[$key]);
          break;
      }
    }
    
    $mapUri = $this->getUri();
    $requestUri = $this->request->getUri();
    
    $cnt = 0;
    $uriBuf = array();
    foreach ($mapUri as $name => $uri) {
      $name = $uri->getName();
      
      if (isset($params[$name])) {
        $value = $params[$name];
        $uriBuf[] = (is_object($value)) ? $value->__toString() : $value;
      } elseif ($uri->isConstant()) {
        $uriBuf[] = $name;
      } elseif ($requestUri->has($cnt)){
        $uriBuf[] = $requestUri->get($cnt);
      }
      
      ++$cnt;
    }
    
    return join('/', $uriBuf);
  }
  
  public function isMatch()
  {
    $mapUri     = $this->getUri();
    $requestUri = $this->request->getUri();
    $reqs       = $this->requirements;
    
    $match = true;
    
    $count = $requestUri->count();
    for ($i = 0; $count; ++$i) {
      $element = $mapUri->getElement($i);
      
      if (!is_object($element)) break;
      $request = $requestUri->get($i);
      
      if ($element->isConstant() && $request === $element->toString()) {
        $match = true;
        continue;
      } elseif ($reqs->hasRequirementByName($element->getName())) {
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
