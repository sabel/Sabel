<?php

abstract class Config
{
  abstract public function get($key);
}

class ConfigImpl extends Config
{
  private $config;

  public function __construct()
  {
    $this->config = Spyc::YAMLLoad('app/configs/config.yml');
  }

  public function get($key)
  {
    return $this->config[$key];
  }
}

?>