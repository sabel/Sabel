<?php

/**
 * Routing request to controller.
 * 
 * @package org.sabel.core
 * @author Mori Reo <mori.reo@gmail.com>
 */
class Sabel_Core_Router
{
  protected $map;
  
  protected function __construct($map)
  {
    $this->map = ($map) ? $map : new Sabel_Controller_Map();
    $this->map->load();
  }
  
  public static function create($map = null)
  {
    return new self($map);
  }
  
  public function routing()
  {
    $entry = $this->map->find();
    //$entry->setRequestUri(new Sabel_Request_Request($entry));
    return $entry;
  }
}