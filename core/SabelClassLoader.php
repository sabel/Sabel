<?php

class SabelClassLoader
{
  private $request;

  private function __construct($request)
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
    if ($this->isValid()) {
      $path = $this->makeControllerPath();
      require_once($path);
      $class = $this->getControllerClassName();
      return new $class();
    } else {
      $path = 'app/modules/Defaults/controllers/Default.php';
      require_once($path);
      return new Defaults_Default();
    }
  }

}

?>
