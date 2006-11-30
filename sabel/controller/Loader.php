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
    return Container::create()->load($this->makeControllerClassPath());
  }
  
  private function makeControllerClassPath()
  {
    $destination = Sabel_Context::getCurrentMapEntry()->getDestination();
    
    $classpath  = $destination->module;
    $classpath .= '.' . trim(Sabel_Core_Const::CONTROLLER_DIR, '/');
    if ($destination->hasController()) {
      $classpath .= '.' . ucfirst($destination->controller);
    } else {
      $classpath .= '.Index';
    }
    
    return $classpath;
  }
}
