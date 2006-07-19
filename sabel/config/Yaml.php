<?php

class Sabel_Config_Yaml
{
  private $data;

  public function __construct($file)
  {
    $parser = new Spyc();
    $this->data = $parser->load($file);
  }

  public function read($key)
  {
    return $this->data[$key];
  }
  
  public function toArray()
  {
    return $this->data;
  }
}

?>