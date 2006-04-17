<?php

interface Response
{
}

class WebResponse implements Response
{
  protected $responses = array();

  public function __set($name, $value)
  {
    $this->responses[$name] = $value;
  }

  public function __get($name)
  {
    return $this->responses[$name];
  }

  public function responses()
  {
    return $this->responses;
  }
}


?>