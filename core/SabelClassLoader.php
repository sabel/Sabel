<?php

class SabelClassLoader
{
  private $request;

  public function __construct($request)
  {
    $this->request = $request;
  }

  public static function create($request)
  {
    return new self($request);
  }

  public function getControllerClassName()
  {
    return $this->request->getModule() . '_' . $this->request->getController();
  }

  public function makeControllerPath()
  {
    $path  = 'app/modules/'  . $this->request->getModule();
    $path .= '/controllers/' . $this->request->getController();
    $path .= '.php';

    return $path;
  }

  public function isValid()
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
    $path = $this->makeControllerPath();

    if ($this->isValid()) {
      require_once($path);
      $class = $this->getControllerClassName();
      return new $class();
    } else {
      return false;
    }
  }

}

?>