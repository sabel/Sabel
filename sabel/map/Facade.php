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
  
  public static function create()
  {
    if (is_null(self::$instance)) self::$instance = new self();
    return self::$instance;
  }
  
  public function setRequestUri($request)
  {
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
    
    if ($matched) {
      return $entry;
    } else {
      $entry = $this->getEntry('default');
      $this->requestUri->initialize($entry);
      return $entry;
    }
  }
  
  public function setEntry($name, $entry)
  {
    $this->entries[$name] = $entry;
  }
  
  public function getEntry($name)
  {
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