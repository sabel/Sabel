<?php

abstract class Config
{
  abstract public function get($key);
}

class ConfigImpl extends Config
{
  private $data;

  public function __construct()
  {
    $this->data = Spyc::YAMLLoad('app/configs/config.yml');
  }

  public function get($key)
  {
    return $this->data[$key];
  }
}

?>