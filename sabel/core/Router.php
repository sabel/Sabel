<?php

/**
 * 
 * note------------------
 * requested /2006/05/05.
 * is it match which controller?
 * how can i decide route it?
 * first, we create simple map object. then compare it and map.
 * map structure look like "match pattern" -> "module controller action"
 * now we can decide where route it if match pattern found.
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

?>