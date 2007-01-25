<?php

final class Sabel_Controller_Plugin
{
  private $plugins = array();
  
  public function add($plugin)
  {
    $this->plugins[] = $plugin;
    return $this;
  }
  
  public function toArray()
  {
    return $this->plugins;
  }
}