<?php

/**
 * Loading controller class.
 *
 * @package sabel.controller
 * @author Mori Reo <mori.reo@gmail.com>
 */
class Sabel_Controller_Loader
{
  public function load()
  {
    $destination = Sabel_Context::getCurrentMapEntry()->getDestination();
    
    $classpath  = $destination->module;
    $classpath .= '.' . trim(Sabel_Const::CONTROLLER_DIR, '/');
    if ($destination->hasController()) {
      $classpath .= '.' . ucfirst($destination->controller);
    } else {
      $classpath .= '.'. ucfirst(Sabel_Const::DEFAULT_CONTROLLER);
    }
    
    return Container::create()->load($classpath);
  }
}