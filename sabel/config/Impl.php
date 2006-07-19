<?php

class Sabel_Config_Yaml
{
  private $data;

  public function __construct()
  {
    $parser = new Spyc();
    $this->data = $parser->load('app/configs/config.yml');
  }

  public function read($key)
  {
    return $this->data[$key];
  }
}

?>