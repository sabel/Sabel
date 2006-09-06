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
  private $destination = null;

  private function __construct($entry)
  {
    $this->entry = $entry;
    $this->destination = $entry->getDestination();
  }

  public static function create($entry)
  {
    return new self($entry);
  }
  
  public function load()
  {
    $c = Container::create();
    $classpath = $this->makeControllerClassPath();
    return $c->load($classpath);
  }
  
  private function makeControllerClassPath()
  {
    $classpath  = $this->destination->module;
    $classpath .= '.' . trim(Sabel_Core_Const::CONTROLLER_DIR, '/');
    $classpath .= '.' . ucfirst($this->destination->controller);
    return $classpath;
  }
}