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
  
  public function __construct($path, $reset = false)
  {
    if ($reset) self::$maps = null;
    if (is_null(self::$maps)) self::$maps = new Sabel_Config_Yaml($path);
  }
  
  public function build($facade = null)
  {
    if (is_null($facade)) {
      $facade = new Sabel_Map_Facade();
      $facade->setRequestUri(new SabeL_Request_Request());
    }
    
    foreach (self::$maps->toArray() as $name => $map) {
      $entry = new Sabel_Map_Entry($name);
      $entry->setUri(new Sabel_Map_Uri($map['uri']));
      
      if (isset($map['requirements'])) {
        foreach ($map['requirements'] as $reqname => $requirement) {
          $entry->setRequirement($reqname, $requirement);
        }
      }
      
      if (isset($map['destination'])) {
        $destination = new Sabel_Map_Destination();
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