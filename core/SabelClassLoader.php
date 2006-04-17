<?php

/**
 * Loading controller class.
 *
 */
class SabelClassLoader
{
  private $request;

  private function __construct($request)
  {
    if ($request instanceof ParsedRequest) {
      $this->request = $request;
    } else {
      throw new SabelException('request is not ParsedRequest');
    }
  }

  public static function create($request)
  {
    return new self($request);
  }

  private function getControllerClassName()
  {
    return $this->request->getModule() . '_' . $this->request->getController();
  }

  protected function makeModulePath()
  {
    $path = 'app/modules/' . $this->request->getModule();
    return $path;
  }

  private function makeControllerPath()
  {
    $path  = 'app/modules/'  . $this->request->getModule();
    $path .= '/controllers/' . $this->request->getController();
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
    $request = new WebRequest();

    if ($this->isValidController()) {
      $path = $this->makeControllerPath();
      require_once($path);
      $class = $this->getControllerClassName();
      return new $class();
    } else if ($this->isValidModule()) {
      $path = 'app/modules/' . $this->request->getModule() . '/controllers/index.php';
      $moduleClassName = $this->request->getModule() . '_Index';
      $request->set('value', $this->request->getController());
      return new $moduleClassName();
    } else {
      $request->set('value', $this->request->getModule());
      $path = 'app/modules/index/controllers/index.php';
      if (is_file($path)) {
        require_once($path);
        return new Index_Index();
      } else {
        throw new SabelException($path . ' is not a valid file');
      }
    }
  }
}

?>
