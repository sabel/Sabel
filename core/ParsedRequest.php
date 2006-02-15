<?php

/**
 * 解析後のリクエスト
 *
 */
class ParsedRequest
{
  private $request;

  public function __construct($request)
  {
    if (!is_array($request))
      throw new Exception("request is not array");

    $this->request = $request;
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

  public function getAction()
  {
    if (!empty($this->request[2])) {
      return $this->request[2];
    } else {
      return SabelConst::DEFAULT_METHOD;
    }
  }

  public function getParameter()
  {
    return $this->request[3];
  }
}

?>