<?php

/**
 * �ڡ�������ȥ���δ��쥯�饹
 *
 * @author Mori Reo <mori.reo@servise.jp>
 * @package sabel.controller
 */
abstract class SabelPageController
{
  public
    $parsedRequest;

  protected
    $parameters,
    $postRequest,
    $template,
    $session;

  /**
   * �Ѿ��襯�饹�Ǽ���
   *
   */
  abstract function initialize();
  abstract function defaults();

  public function setup($parsedRequest)
  {
    $this->setParsedRequest($parsedRequest);
    $this->postRequest = new PostRequest();
    $this->setTemplate(new HtmlTemplate());
    $this->session = SessionManager::makeInstance();
  }

  public function execute($methodName)
  {
    $this->$methodName();
    $this->initTemplate();
    $this->showTemplate();
  }

  public function hasMethod($name)
  {
    if (method_exists($this, $name)) {
      return true;
    } else {
      return false;
    }
  }

  protected function setTemplate($template)
  {
    $this->template = $template;
  }

  protected function setParsedRequest($request)
  {
    $this->parsedRequest = $request;
  }

  protected function showActionMethods()
  {
    print "<pre>";

    $methods = get_class_methods($this);
    foreach ($methods as $key => $val) {
      if ($val[0] != '_') print $val . "<br/>\n";
    }

    print "</pre>";
  }

  protected function checkReferer($validURIs)
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
  protected function redirect($to)
  {
    $absolute  = 'http://' . $_SERVER['HTTP_HOST'];
    $redirect  = 'Location: ' . $absolute . $to;
    header($redirect);

    exit; // HTTP�إå�(30x)������������Ͻ������³���ʤ�
  }

  /**
   * forwaring anothor controller or method of same controller.
   *
   */
  protected function forward($to)
  {
    // @todo implement
  }

  protected function initTemplate()
  {
    $d = TemplateDirectorFactory::create($this->parsedRequest);
    $this->template->selectPath($d->decidePath());
    $this->template->selectName($d->decideName());
  }

  /**
   * process template then rendering it.
   *
   */
  protected function showTemplate()
  {
    try {
      $this->template->rendering();
    } catch(SabelException $e) {
      $e->printStackTrace();
    }
  }
}

?>