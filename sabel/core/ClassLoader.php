<?php

/**
 * Loading controller class.
 *
 */
class Sabel_Core_ClassLoader
{
  private $destination;

  private function __construct($destination)
  {
    $this->destination = $destination;
  }

  public static function create($d)
  {
    return new self($d);
  }

  private function getControllerClassName()
  {
    return $this->destination[0] . '_' . $this->destination[1];
  }

  protected function makeModulePath()
  {
    return RUN_BASE . '/app/modules/' . $this->destination[0];
  }

  private function makeControllerPath()
  {
    $path  = RUN_BASE.'/app/modules/'  . $this->destination[0];
    $path .= '/controllers/' . $this->destination[1];
    $path .= '.php';

    return $path;
  }

  protected function isValidModule()
  {
    if (is_dir($this->makeModulePath())) {
      return true;
    } else {
      return false;
    }
  }
  
  protected function isValidController()
  {
    $path = $this->makeControllerPath();
    
    if (is_file($path)) {
      return true;
    } else {
      return false;
    }
  }
  
  public function load()
  {
    if ($this->isValidController()) {
      $path = $this->makeControllerPath();
      require_once($path);
      $class = $this->getControllerClassName();
      return new $class();
    } else if ($this->isValidModule()) {
      $path = RUN_BASE.'/app/modules/' . $this->destination[0] . '/controllers/index.php';
      require_once($path);
      $moduleClassName = $this->destination[0] . '_Index';
      if (class_exists($moduleClassName)) {
        return new $moduleClassName();
      } else {
        throw new Sabel_Exception_Runtime('can\'t found out controller class: ' . $moduleClassName);
      }
    } else {
      $path = RUN_BASE.'/app/modules/Index/controllers/index.php';
      if (is_file($path)) {
        require_once($path);
        return new Index_Index();
      } else {
        throw new Sabel_Exception_Runtime($path . ' is not a valid file');
      }
    }
  }
}

?>