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
    $destination = $r->routing();
    $class = Sabel_Controller_Loader::create($destination)->load();
    $class->setup(new Sabel_Request_Request($destination->getEntry()), $destination);
    $class->execute();
  }
}