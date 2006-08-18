<?php

uses('sabel.config.Spyc');
uses('sabel.controller.map.Entry');

/**
 * Sabel_Controrller_Map
 * 
 * @package org.sabel
 * @author Mori Reo <mori.reo@gmail.com>
 */
class Sabel_Controller_Map
{
  const DEFAULT_PATH = '/config/map.yml';
  
  protected $path = '';
  protected static $map = array();
  protected $requestUri = null;
  
  public function __construct($path = self::DEFAULT_PATH)
  {
    $this->path = RUN_BASE . $path;
  }
  
  public static function getDefaultModule()
  {
    return self::$map['module'];
  }
  
  public static function getDefaultController()
  {
    return self::$map['controller'];
  }
  
  public static function getDefaultAction()
  {
    return self::$map['action'];
  }
  
  public function getPath()
  {
    return $this->path;
  }
  
  public function load()
  {
    if (is_file($this->getPath())) {
      $c = new Sabel_Config_Yaml($this->getPath());
      self::$map = $c->toArray();
    } else {
      throw new Exception("map configure not found on " . $this->getPath());
    }
  }
  
  public function setRequestUri($uri)
  {
    $this->requestUri = $uri;
  }
  
  public function find()
  {
    $requestUri = $this->requestUri;
    
    foreach ($this->getEntries() as $entry) {
      if ($entry->getUri()->count() === $requestUri->count()) {
        $matched = $entry;
        break;
      }
    }
    
    return (is_object($matched)) ? $matched : $this->getEntry('default');
  }
  
  public function getEntry($name)
  {
    return new Sabel_Controller_Map_Entry(self::$map[$name], $this->requestUri);
  }
  
  public function getEntries()
  {
    $entries = array();
    foreach (self::$map as $name => $entry) {
      if ($name !== 'module' && $name !== 'controller' && $name !== 'action') {
        $entries[] = new Sabel_Controller_Map_Entry($entry, $this->requestUri);
      }
    }
    return $entries;
  }
  
  public function getEntriesByCount($number)
  {
    $number =(int) $number;
    
    $entries = array();
    foreach (self::$map as $entry) {
      $entry = new Sabel_Controller_Map_Entry($entry, $this->requestUri);
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
        // @todo 仕様決めませう
      }
    }
    
    return (is_object($hasConstant)) ? $hasConstant : false;
  }
}