<?php

class Sabel_Config_Yaml
{
  private $data;

  public function __construct($file)
  {
    $parser = Sabel::load('Sabel_Config_Spyc');
    $this->data = $parser->load($file);
  }
  
  public function isValid()
  {
    return (count($this->data) === 0) ? false : true;
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
