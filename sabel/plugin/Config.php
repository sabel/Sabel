<?php

abstract class Sabel_Plugin_Config
{
  private $plugins = array();
  
  abstract function configure();
  
  public function add($plugin)
  {
    $this->plugins[] = $plugin;
    return $this;
  }
  
  public function plugins()
  {
    return $this->plugins;
  }
}
