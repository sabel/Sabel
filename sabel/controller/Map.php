<?php

/**
 * Sabel_Controrller_Map
 *
 * @package org.sabel
 * @author Mori Reo <mori.reo@gmail.com>
 */
class Sabel_Controller_Map implements Iterator
{
  const DEFAULT_PATH = '/config/map.yml';
  
  protected $path = '';
  protected $map  = array();
  protected $requestUri = null;
  
  protected $position = 0;
  protected $entries  = array();
  
  static protected $instance = null;
  
  public function __construct($path = self::DEFAULT_PATH)
  {
    $this->path = RUN_BASE . $path;
  }
  
  public static function create()
  {
    if (is_null(self::$instance)) self::$instance = new self();
    return self::$instance;
  }
  
  public function setConfigPath($path)
  {
    $this->path = $path;
  }
  
  public function load()
  {
    if (ENVIRONMENT === 'development') {
      if (!is_file($this->getPath()))
        throw new Exception("map configure not found on " . $this->getPath());
      
      $c = new Sabel_Config_Yaml($this->getPath());
      $this->map = $c->toArray();
      $this->entries = $this->getEntries();
    } else {
      $cache = new Sabel_Cache_Apc();
      if (!($this->map = $cache->read('map'))) {
        if (!is_file($this->getPath()))
          throw new Exception("map configure not found on " . $this->getPath());
          
        $c = new Sabel_Config_Yaml($this->getPath());
        $this->map = $c->toArray();
        $cache->write('map', $this->map);
        
        $this->entries = $this->getEntries();
      }
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
    // @todo implement rules of found out correct map entry.
    return $this->getEntry('default');
  }
  
  public function getEntry($name)
  {
    if (!is_object($this->requestUri)) throw new Sabel_Exception_Runtime("");
    
    $entry = new Sabel_Controller_Map_Entry($name, $this->map[$name]);
    $this->requestUri->initializeRequestUriAndParameters();
    $this->requestUri->initialize($entry);
    $entry->setRequest($this->requestUri);
    return $entry;
  }
  
  public function getEntries()
  {
    $entries = array();
    foreach (array_keys($this->map) as $name) $entries[] = $this->getEntry($name);
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