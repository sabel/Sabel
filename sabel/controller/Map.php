<?php

/**
 * Sabel_Controrller_Map
 *
 * @package org.sabel
 * @author Mori Reo <mori.reo@gmail.com>
 */
class Sabel_Controller_Map implements Iterator
{
  protected $map = array();
  protected $requestUri = null;
  
  protected $position = 0;
  protected $entries  = array();
  
  static protected $instance = null;
  
  public static function create($map)
  {
    if (is_null(self::$instance)) self::$instance = new self();
    self::$instance->setMap($map);
    return self::$instance;
  }
  
  public function setMap($map)
  {
    $this->map = $map;
  }
  
  public function setRequestUri($request)
  {
    $this->requestUri = $request;
    $this->requestUri->initializeRequestUriAndParameters();
    $this->entries = $this->getEntries();
  }
  
  public function find()
  {
    // @todo implement rules of found out correct map entry.
    // return $this->getEntry('default');
    foreach ($this->entries as $entry) {
      $entry->isMatch();
    }
    
    return $this->getEntry('default');
  }
  
  public function getEntry($name)
  {
    if (!is_object($this->requestUri))
      throw new Sabel_Exception_Runtime("RequestUri object not found.");
    
    $entry = new Sabel_Controller_Map_Entry($name, $this->map[$name]);
    $this->requestUri->initialize($entry);
    $entry->setRequest($this->requestUri);
    return $entry;
  }
  
  public function getEntries()
  {
    $entries = array();
    foreach (array_keys($this->map) as $name) {
      $entries[] = $this->getEntry($name);
    }
    return $entries;
  }
  
  public function getEntriesByCount($number)
  {
    $number =(int) $number;
    
    $entries = array();
    foreach (array_keys($this->map) as $name) {
      $entry = $this->getEntry($name);
      if ($entry->getUri()->count() === $number) $entries[] = $entry;
    }
    return $entries;
  }
  
  public function hasSameUriCountOfEntries($number)
  {
    $entries = $this->getEntriesByCount($number);
    return (count($entries) >= 2) ? count($entries) : false;
  }
  
  public function getEntryByHasConstantUriElement($number)
  {
    $entries = $this->getEntriesByCount($number);
    foreach ($entries as $entry) {
      if ($entry->getUri()->getElement(0)->isConstant()) {
        $hasConstant = $entry;
        break;
        // @todo decide requirement for map component.
      }
    }
    
    return (is_object($hasConstant)) ? $hasConstant : false;
  }
  
  /**
   * implements for Iterator interface
   *
   */
  public function current() {
    return $this->entries[$this->position];
  }
  
  /**
   * implements for Iterator interface
   *
   */
  public function key()
  {
    return $this->position;
  }
  
  /**
   * implements for Iterator interface
   *
   */
  public function next()
  {
    return $this->position++;
  }
  
  /**
   * implements for Iterator interface
   *
   */
  public function rewind()
  {
    $this->position = 0;
  }
  
  /**
   * implements for Iterator interface
   *
   */
  public function valid()
  {
    return ($this->position < count($this->entries));
  }
}