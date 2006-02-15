<?php

/**
 * ページコントローラの基底クラス
 *
 * @author Mori Reo <mori.reo@servise.jp>
 * @package sabel.controller
 */
abstract class SabelPageController
{
  protected $parameters;
  protected $request;

  /**
   * 継承先クラスで実装
   *
   */
  abstract function initialize();
  abstract function defaults();

  public function setup()
  {
    $this->request = new PostRequest();
  }

  public function __set($name, $value)
  {
    $this->parameters[$name] = $value;
  }

  public function __get($name)
  {
    return $this->parameters[$name];
  }

  public function showActionMethods()
  {
    print "<pre>";

    $methods = get_class_methods($this);
    foreach ($methods as $key => $val) {
      if ($val[0] != '_') print $val . "<br/>\n";
    }

    print "</pre>";
  }

  public function checkReferer($validURIs)
  {
    $ref = $_SERVER['HTTP_REFERER'];
    $absolute = 'http://'.$_SERVER['HTTP_HOST'] . $validURIs[0];
    if ($ref == $absolute) {
      return true;
    } else {
      return false;
    }
  }

  /**
   * HTTP Redirect to another location.
   * this method will be avoid "back button" problem.
   *
   * @param string $to /Module/Controller/Method
   */
  public function redirect($to)
  {
    $absolute  = 'http://' . $_SERVER['HTTP_HOST'];
    $absolute .= $to;
    $redirect = 'Location: ' . $absolute;
    header($redirect);

    // HTTPヘッダ(30x)を送信した後は処理を継続しない
    exit;
  }

  /**
   * forwaring anothor controller or method of same controller
   *
   */
  public function forward($to)
  {
    // @todo implement
  }

  public function hasMethod($name)
  {
    if (method_exists($this, $name)) {
      return true;
    } else {
      return false;
    }
  }

  public function execute($methodName)
  {
    $this->$methodName();
  }
}

?>