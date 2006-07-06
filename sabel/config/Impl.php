<?php

class Sabel_Config_Impl
{
  private $data;

  public function __construct()
  {
    $parser = new Spyc();
    $this->data = $parser->load('app/configs/config.yml');
  }

  public function get($key)
  {
    return $this->data[$key];
  }
}

?>