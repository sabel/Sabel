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
    $map = new Sabel_Core_Map();
    return $map->connect($request_uri);
  }
}

/**
 * Sabel_Core_Map
 * 
 * @package org.sabel
 * @author Mori Reo <mori.reo@gmail.com>
 */
class Sabel_Core_Map 
{
  protected $map;
  
  public function __construct()
  {
    $tmpMap = array();
    $tmpMap['pattern'] = array('([1-2][0-9]{3})', '([0-1]?[0-9])', '([0-3]?[0-9])');
    $tmpMap['destination'] = array('blog', 'common', 'show');
    $this->map[] = $tmpMap;
  }
  
  public function connect($request_uri)
  {
    $pattern = $this->map[0]['pattern'];
    $pattern = join('/', $pattern);
    $pattern = '%'.$pattern.'%';
    if (preg_match($pattern, $request_uri, $matchs)) {
      return $this->map[0]['destination'];
    }
  }
}

?>