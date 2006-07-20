<?php

/**
 * Routing request to controller.
 * 
 * @package org.sabel.core
 * @author Mori Reo <mori.reo@gmail.com
 */
class Sabel_Core_Router
{
  public function routing($request_uri)
  {
    $rcount = count(explode('/', $request_uri));
    
    $map = new Sabel_Controller_Map();
    $map->load();
    foreach ($map->getEntries() as $entry) {
      if ($entry->getUri()->count() === $rcount) {
        return $entry->getDestination();
      }
    }
  }
}