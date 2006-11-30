<?php

/**
 * Sabel_Facade
 *
 * @package org.sabel
 * @author Mori Reo <mori.reo@gmail.com>
 */
class Sabel_Map_Facade implements Iterator
{
  protected $requestUri = null;
  
  protected $position = 0;
  protected $entries  = array();
  
  static protected $instance = null;
  
  protected $entry = null;
  
  public static function create()
  {
    if (self::$instance === null) self::$instance = new self();
    return self::$instance;
  }
  
  public function setRequestUri($request)
  {
    if (!$request instanceof Sabel_Request_Request)
      throw new Sabel_Exception_Runtime("request is not instance of Sabel_Request_Request");
      
    $this->requestUri = $request;
    $this->requestUri->initializeRequestUriAndParameters();
  }
  
  public function find()
  {
    $matched = false;
    $entries = $this->entries;
    foreach ($entries as $entry) {
      $this->requestUri->initialize($entry);
      $entry->setRequest($this->requestUri);
      if ($entry->isMatch()) {
        $matched = true;
        break;
      }
    }
    
    if (!$matched) {
      $entry = $this->getEntry('default');
      $this->requestUri->initialize($entry);
    }
    
    $this->entry = $entry;
    return $entry;
  }
  
  public function getCurrentEntry()
  {
    if (!$this->entry instanceof Sabel_Map_Entry)
      throw new Sabel_Exception_Runtime("entry is not instance of valid Class");
      
    return $this->entry;
  }
  
  public function setEntry($name, $entry)
  {
    $this->entries[$name] = $entry;
  }
  
  public function getEntry($name)
  {
    if (!isset($this->entries[$name]))
      throw new Sabel_Exception_Runtime("{$name} entry does't exists in map entries");
    
    $entry = $this->entries[$name];
    $entry->setRequest($this->requestUri);
    return $entry;
  }
  
  public function getEntriesByCount($number)
  {
    $number =(int) $number;
    
    $entries = array();
    foreach (array_keys($this->entries) as $name) {
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
    $entries = array_values($this->entries);
    return $entries[$this->position];
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