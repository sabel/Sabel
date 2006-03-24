<?php

interface Request
{
  public function get($key);
  public function set($key, $value);
  public function getRequests();
}

class WebRequest implements Request
{
  protected $parsedRequest;
  protected $parameters;

  public function __construct()
  {
    $this->parsedRequest = ParsedRequest::create();
    $this->parameters = new Parameters($this->parsedRequest->getParameter());
  }

  public function __get($name)
  {
    if ($name == 'requests') {
      return $this->getRequests();
    } else if ($name == 'parameter') {
      return $this->getParameter();
    } else if ($name == 'parameters') {
      return $this->parameters;
    } else {
      return $this->get($name);
    }
  }

  public function get($key)
  {
    if (isset($_POST[$key])) {
      return Sanitize::normalize($_POST[$key]);
    } else {
      return false;
    }
  }

  public function getRequests()
  {
    $array = array();
    foreach ($_POST as $key => $value) {
      $array[$key] = (isset($value)) ? Sanitize::normalize($value) : null;
    }
    return $array;
  }

  public function set($key, $value)
  {
    $_POST[$key] = $value;
  }

  public function getParameter()
  {
    return $this->parsedRequest->getParameter();
  }
}

?>
