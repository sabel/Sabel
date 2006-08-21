<?php

uses('sabel.core.Utility');

/**
 * Sabel_Request_Uri
 * 
 * @package org.sabel
 * @author Mori Reo <mori.reo@gmail.com>
 */
class Sabel_Request_Uri
{
  /**
   * @var array parts of uri. separate by slash (/)
   */
  protected $parts = array();
  protected $entry = null;
  
  public function __construct($requestUri, $entry)
  {
    $this->parts = explode('/', $requestUri);
    $this->entry = $entry;
  }
  
  public function count()
  {
    return count($this->parts);
  }
  
  public function get($pos)
  {
    return (isset($this->parts[$pos])) ? $this->parts[$pos] : null;
  }
  
  public function getModule()
  {
    return $this->get($this->entry->getUri()->calcElementPositionByName('module'));
  }
  
  public function getController()
  {
    return $this->get($this->entry->getUri()->calcElementPositionByName('controller'));
  }
  
  public function getAction()
  {
    return $this->get($this->entry->getUri()->calcElementPositionByName('action'));
  }
  
  public function getByName($name)
  {
    if (is_null($this->entry)) throw new Exception('entry is null.');
    $position = $this->entry->getUri()->calcElementPositionByName($name);
    return $this->get($position);
  }
  
  public function has($pos)
  {
    return isset($this->parts[$pos]);
  }
}