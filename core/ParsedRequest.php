<?php

class ParsedRequest
{
  private $request;

  public function __construct($request)
  {
    $this->request = $request;
  }

  public function getModule()
  {
    if (!empty($this->request[0]) && $this->request[0] != "") {
      return $this->request[0];
    } else {
      return 'Defaults';
    }
  }

  public function getController()
  {
    if (isset($this->request[1]) && $this->request[1] != "") {
      return $this->request[1];
    } else {
      return 'Default';
    }
  }

  public function getAction()
  {
    if (isset($this->request[2]) && $this->request[2] != "") {
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
