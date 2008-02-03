<?php

abstract class Sabel_Bus_Config extends Sabel_Object
{
  protected $processors = array();
  protected $configs    = array();
  
  public function getProcessors()
  {
    return $this->processors;
  }
  
  public function getConfigs()
  {
    return $this->configs;
  }
}
