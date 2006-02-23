<?php

/**
 * ページコントローラの基底クラス
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
    $template;

  /**
   * 継承先クラスで実装
   *
   */
  abstract function initialize();
  abstract function defaults();

  public function setup($parsedRequest)
  {
    $this->setParsedRequest($parsedRequest);
    $this->postRequest = new PostRequest();
    $this->setTemplate(new HtmlTemplate());
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
    $methods = get_class_methods($this);

    foreach ($methods as $key => $val) {
      if ($val[0] != '_') print $val . "<br/>\n";
    }
  }

  protected function checkReferer($validURIs)
  {
    $ref = $_SERVER['HTTP_REFERER'];
    $replaced = preg_replace('/\\//', '\/', $validURIs[0]);
    $patternAbsoluteURI = '/http:\/\/' . $_SERVER['HTTP_HOST'] . $replaced . '/';
    preg_match($patternAbsoluteURI, $ref, $matchs);
    return (isset($matchs[0])) ? true : false;
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

    exit; // HTTPヘッダ(30x)を送信した後は処理を継続しない
  }

  /**
   * forwaring anothor controller or method of same controller.
   *
   */
  protected function forward($to)
  {
    // @todo implement
  }

  /**
   * テンプレートを初期化
   */
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