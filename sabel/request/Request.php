<?php

interface Request
{
  public function get($key);
  public function set($key, $value);
  public function getRequests();
}

class Sabel_Request_Request
{
  public function test()
  {
    
  }
}

class Sabel_Request implements Request
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
      $ret = Sanitize::normalize($_POST[$key]);
      return $this->convertToEUC($ret);
    } else {
      return null;
    }
  }

  public function getRequests()
  {
    $array = array();
    foreach ($_POST as $key => $value) {
      $val = (isset($value)) ? Sanitize::normalize($value) : null;
      if ($key != '_') {
        $array[$key] = $this->convertToEUC($val);
      }
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

  protected function convertToEUC($value)
  {
    if (is_array($value)) {
      foreach($value as $k => $v) {
        $enc       = mb_detect_encoding($v, 'UTF-8, EUC_JP, SJIS');
        $v         = mb_convert_kana($v, 'KV', $enc);
        $value[$k] = mb_convert_encoding($v, 'EUC_JP', $enc);
      }
    } else {
      $enc   = mb_detect_encoding($value, 'UTF-8, EUC_JP, SJIS');
      $value = mb_convert_kana($value, 'KV', $enc);
      $value = mb_convert_encoding($value, 'EUC_JP', $enc);
    }

    return $value;
  }
}

?>
