<?php

/**
 * Loading controller class.
 *
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

  private function getControllerClassName()
  {
    return $this->destination->module . '_' . $this->destination->controller;
  }

  protected function makeModulePath()
  {
    return RUN_BASE . Sabel_Core_Const::MODULES_DIR . $this->destination->module;
  }

  private function makeControllerPath()
  {
    $path  = $this->makeModulePath();
    $path .= Sabel_Core_Const::CONTROLLER_DIR . $this->destination->controller;
    $path .= '.php';
    return $path;
  }

  protected function isValidModule()
  {
    return (is_dir($this->makeModulePath()));
  }
  
  protected function isValidController()
  {
    return (is_file($this->makeControllerPath()));
  }
  
  public function load()
  {
    $c = Container::create();
    if ($this->isValidController()) {
      $class = $this->getControllerClassName();
      return $c->load($class);
    } else if ($this->isValidModule()) {
      $path = RUN_BASE.Sabel_Core_Const::MODULES_DIR . $this->destination->controller . '/controllers/index.php';
      $moduleClassName = $this->destination->module . '_Index';
      if (class_exists($moduleClassName)) {
        return new $moduleClassName();
      } else {
        throw new Sabel_Exception_Runtime('can\'t found out controller class: ' . $moduleClassName);
      }
    } else {
      $path = RUN_BASE.'/app/index/controllers/index.php';
      if (is_file($path)) {
        //require_once($path);
        return new Index_Index();
      } else {
        throw new Sabel_Exception_Runtime($path . ' is not a valid file');
      }
    }
  }
}