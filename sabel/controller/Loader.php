<?php

/**
 * Loading controller class.
 *
 * @package sabel.controller
 * @author Mori Reo <mori.reo@gmail.com>
 */
class Sabel_Controller_Loader
{
  private $entry = null;

  private function __construct($entry)
  {
    $this->entry = $entry;
  }

  public static function create($entry)
  {
    return new self($entry);
  }
  
  public function load()
  {
    return Container::create()->load($this->makeControllerClassPath());
  }
  
  private function makeControllerClassPath()
  {
    $destination = $this->entry->getDestination();
    
    $classpath  = $destination->module;
    $classpath .= '.' . trim(Sabel_Core_Const::CONTROLLER_DIR, '/');
    $classpath .= '.' . ucfirst($destination->controller);
    
    return $classpath;
  }
}