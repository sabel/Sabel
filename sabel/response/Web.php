<?php

uses('sabel.response.Interface');

class Sabel_Response_Web implements Sabel_Response_Interface
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
