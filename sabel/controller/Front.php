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
    $entry = Sabel_Core_Router::create()->routing();
    $class = Sabel_Controller_Loader::create($entry)->load();
    $class->execute();
  }
}