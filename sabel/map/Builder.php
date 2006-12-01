<?php

/**
 * Sabel_Map_Builder
 * 
 * @package org.sabel
 * @author Mori Reo <mori.reo@gmail.com>
 */
class Sabel_Map_Builder
{
  protected static $maps = null;
  
  public function __construct($path = null, $reset = false)
  {
    if ($path === null) return 0;
    
    if ($reset) self::$maps = null;
    
    $bcpath = RUN_BASE . '/cache/map.cache';
    
    if (is_readable($bcpath)) {
      self::$maps = unserialize(file_get_contents($bcpath));
    } else {
      if (self::$maps === null) self::$maps = Sabel::load('Sabel_Config_Yaml', $path);
      
      if (is_writable($bcpath)) {
        file_put_contents($bcpath, serialize(self::$maps));
      } elseif (($fp = fopen($bcpath, 'w+'))) {
        fwrite($fp, serialize(self::$maps));
        fclose($fp);
      } else {
        throw Sabel::load('Sabel_Exception_Runtime', $bcpath . " can't open.");
      }
    }
  }
  
  public function load($path)
  {
    self::$maps = Sabel::load('Sabel_Config_Yaml', $path);
  }
  
  public function build($facade = null)
  {
    if ($facade === null) {
      $facade = Sabel::load('Sabel_Map_Facade');
      $facade->setRequestUri(Sabel::load('Sabel_Request'));
    }
    
    foreach (self::$maps->toArray() as $name => $map) {
      $entry = Sabel::load('Sabel_Map_Entry', $name);
      $entry->setUri(Sabel::load('Sabel_Map_Uri', $map['uri']));
      
      if (isset($map['requirements'])) {
        foreach ($map['requirements'] as $reqname => $requirement) {
          $entry->setRequirement($reqname, $requirement);
        }
      }
      
      if (isset($map['destination'])) {
        $destination = Sabel::load('Sabel_Map_Destination');
        $destination->setModule($map['destination']['module']);
        $destination->setController($map['destination']['controller']);
        $destination->setAction($map['destination']['action']);
        $entry->setDestination($destination);
      }
      
      $facade->setEntry($name, $entry);
    }
    
    return $facade;
  }
}