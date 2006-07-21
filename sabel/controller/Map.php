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
  
  protected $path;
  protected $map;
  
  public function __construct($path = null)
  {
    $this->path = RUN_BASE . (($path) ? $path : self::DEFAULT_PATH);
  }
  
  public function getPath()
  {
    return $this->path;
  }
  
  public function load()
  {
    if (is_file($this->getPath())) {
      $c = new Sabel_Config_Yaml($this->getPath());
      $this->map = $c->toArray();
    } else {
      throw new Exception("map configure not found on " . $this->getPath());
    }
  }
  
  public function getEntry($name)
  {
    return new Sabel_Controller_Map_Entry($this->map[$name]);
  }
  
  public function getEntries()
  {
    $entries = array();
    foreach ($this->map as $entry) {
      $entries[] = new Sabel_Controller_Map_Entry($entry);
    }
    return $entries;
  }
  
  public function getEntriesByCount($number)
  {
    $number = (int) $number;
    
    $entries = array();
    foreach ($this->map as $entry) {
      $entry = new Sabel_Controller_Map_Entry($entry);
      if ($entry->getUri()->count() === $number) $entries[] = $entry;
    }
    return $entries;
  }
  
  public function hasSameUriCountOfEntries($number)
  {
    $number  = (int) $number;
    $entries = $this->getEntriesByCount($number);
    return (count($entries) >= 2) ? count($entries) : false;
  }
  
  public function getEntryByHasConstantUriElement($number)
  {
    $entries = $this->getEntriesByCount($number);
    foreach ($entries as $entry) {
      if ($entry->getUri()->getElement(0)->isConstant()) {
        $hasConstant = $entry;
      }
    }
    
    return (is_object($hasConstant)) ? $hasConstant : false;
  }
}