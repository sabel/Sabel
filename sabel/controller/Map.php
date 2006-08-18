
<?php

uses('sabel.config.Spyc');
uses('sabel.controller.map.Entry');

/**
 * Sabel_Controrller_Map
 *
 * @package org.sabel
 * @author Mori Reo <mori.reo@gmail.com>
 */
class Sabel_Controller_Map implements Iterator
{
  const DEFAULT_PATH = '/config/map.yml';
  
  protected $path       = '';
  protected static $map = array();
  protected $requestUri = null;
  
  protected $position = 0;
  protected $entries  = array();
  
  public function __construct($path = self::DEFAULT_PATH)
  {
    $this->path = RUN_BASE . $path;
  }
  
  public function load()
  {
    if (is_file($this->getPath())) {
      $c = new Sabel_Config_Yaml($this->getPath());
      self::$map = $c->toArray();
      
      $this->entries = $this->getEntries();
    } else {
      throw new Exception("map configure not found on " . $this->getPath());
    }
  }
  
  public function getPath()
  {
    return $this->path;
  }
  
  public function setRequestUri($uri)
  {
    $this->requestUri = $uri;
  }
  
  public function find()
  {
    $matched = $this->getEntriesByCount($this->requestUri->count());
    
    return (is_object($matched[0])) ? $matched[0] : $this->getEntry('default');
  }
  
  public function getEntry($name)
  {
    return new Sabel_Controller_Map_Entry($name, self::$map[$name], $this->requestUri);
  }
  
  public function getEntries()
  {
    $entries = array();
    foreach (array_keys(self::$map) as $name) $entries[] = $this->getEntry($name);
    return $entries;
  }
  
  public function getEntriesByCount($number)
  {
    $number =(int) $number;
    
    $entries = array();
    foreach (array_keys(self::$map) as $name) {
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