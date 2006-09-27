<?php

/**
 * Sabel_Map_Builder
 * 
 * @package org.sabel
 * @author Mori Reo <mori.reo@gmail.com>
 */
class Sabel_Map_Builder
{
  public function __construct()
  {
    
  }
  
  public function build($path, $facade = null)
  {
    $maps = new Sabel_Config_Yaml($path);
    
    if (is_null($facade)) {
      $facade = new Sabel_Map_Facade();
      $facade->setRequestUri(new SabeL_Request_Request());
    }
    
    foreach ($maps->toArray() as $name => $map) {
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