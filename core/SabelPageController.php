<?php

/**
 * �ڡ�������ȥ���δ��쥯�饹
 *
 * @author Mori Reo <mori.reo@servise.jp>
 * @package sabel.controller
 */
abstract class SabelPageController
{
  protected $parameters;
  protected $request;
  public $rawRequest;
  public $template;

  /**
   * �Ѿ��襯�饹�Ǽ���
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

    // HTTP�إå�(30x)������������Ͻ������³���ʤ�
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
    $this->showTemplate();
  }

  /**
   * process template then rendering it.
   *
   */
  protected function showTemplate()
  {
    $d = TemplateDirectorFactory::create($this->rawRequest);
    $this->template->selectPath($d->decidePath());
    $this->template->selectName($d->decideName());

    try {
      $this->template->rendering();
    } catch(SabelException $e) {
      $e->printStackTrace();
    }
  }
}

?>