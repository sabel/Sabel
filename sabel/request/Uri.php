<?php

/**
 * Sabel_Request_Uri
 * 
 * @package org.sabel
 * @author Mori Reo <mori.reo@gmail.com>
 */
class Sabel_Request_Uri
{
  /**
   * @var Array parts of uri. separate by slash (/)
   */
  protected $parts = array();
  protected $entry = null;
  
  public function __construct($requestUri, $entry = null)
  {
    $this->parts = explode('/', $requestUri);
    (!is_null($entry)) ? $this->setEntry($entry) : '';
  }
  
  public function setEntry($entry)
  {
    $this->entry = $entry;
  }
  
  public function __get($key)
  {
    $value = $this->getByName($key);
    if (is_numeric($value)) {
      if (is_float($value)) {
        return (float) $value;
      } else {
        return (int) $value;
      }
    } else {
      return (string) $value;
    }
  }
  
  public function count()
  {
    return count($this->parts);
  }
  
  public function get($pos)
  {
    return ($this->has($pos)) ? $this->parts[$pos] : null;
  }
  
  public function has($pos)
  {
    return isset($this->parts[$pos]);
  }
  
  public function getModule()
  {
    return $this->getByName('module');
  }
  
  public function getController()
  {
    return $this->getByName('controller');
  }
  
  public function getAction()
  {
    return $this->getByName('action');
  }
  
  public function getByName($name)
  {
    if (is_null($this->entry)) throw new Exception('entry is null.');
    $position = $this->entry->getUri()->calcElementPositionByName($name);
    return $this->get($position);
  }
}