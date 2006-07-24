<?php

/**
 * Routing request to controller.
 * 
 * @package org.sabel.core
 * @author Mori Reo <mori.reo@gmail.com
 */
class Sabel_Core_Router
{
  protected $map;
  
  public function __construct($map = null)
  {
    $this->map = ($map) ? $map : new Sabel_Controller_Map();
    $this->map->load();
  }
  
  public function routing($uri)
  {
    $entry = $this->map->getEntryByHasConstantUriElement(2);
    if ($entry->getUri()->getElement(0)->getConstant() === $uri->get(0)) {
      return $entry->getDestination();
    }
    
    foreach ($this->map->getEntries() as $entry) {
      if ($entry->getUri()->count() === $uri->count()) {
        return $entry->getDestination();
      }
    }
  }
}