<?php

/**
 * 解析後のリクエスト
 *
 */
class ParsedRequest
{
  private $request;

  private static $instance = null;

  public static function create()
  {
    if (!self::$instance) {
      self::$instance = new self();
    }
    return self::$instance;
  }

  protected function __construct()
  {
    $this->request = $this->parse();
  }

  protected function parse()
  {
    global $sabelfilepath;

    $uri = $_SERVER['REQUEST_URI'];

    $path = split('/', $sabelfilepath);
    array_shift($path);
    foreach ($path as $p => $v) {
      if ($v == $path[count($path) - 2]) {
        $dir = $v;
      }
    }

    $sp = split('/', $uri);
    array_shift($sp);

    $request = array();
    $matched = true;
    foreach ($sp as $p => $v) {
      if ($matched) $request[] = $v;

      // neccesary for when application is not root.
      // if ($v == $dir) $matched = true;
    }

    return $request;
  }

  public function getModule()
  {
    if (!empty($this->request[0])) {
      return $this->request[0];
    } else {
      return SabelConst::DEFAULT_MODULE;
    }
  }

  public function getController()
  {
    if (!empty($this->request[1])) {
      return $this->request[1];
    } else {
      return SabelConst::DEFAULT_CONTROLLER;
    }
  }

  public function getMethod()
  {
    if (!empty($this->request[2])) {
      return $this->request[2];
    } else {
      return SabelConst::DEFAULT_METHOD;
    }
  }

  public function getParameter()
  {
    if (!empty($this->request[3])) {
      return $this->request[3];
    } else {
      return null;
    }
  }
}

?>
