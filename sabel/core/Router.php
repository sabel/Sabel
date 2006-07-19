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
    uses('sabel.config.Spyc');
    $c = new Sabel_Config_Yaml(RUN_BASE.'/config/map.yml');
    
    $rcount = count(explode('/', $request_uri));
    
    $map = $c->toArray();
    foreach ($map as $config) {
      $ccount = count(explode('/', $config['uri']));
      if ($ccount === $rcount) {
        return $config['destination'];
      }
    }
  }
}

?>
