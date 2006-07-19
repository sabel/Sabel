<?php

/**
 * Front Controller Class.
 *
 * @package org.sabel
 * @author Mori Reo <mori.reo@gmail.com>
 */
class Sabel_Controller_Front
{
  public function ignition()
  {
    $r = new Sabel_Core_Router();
    $destination = $r->routing(Sabel_Request_URI::getUri());
    Sabel_Controller_Loader::create($destination)->load();
    $className = ucfirst($destination[0]) . '_' . ucfirst($destination[1]);
    Sabel_Core_Context::log('create controller: '.$className);
    $class = new $className();
    $action = $destination[2];
    
    $class->$action();
  }
}

?>