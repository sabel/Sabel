<?php

abstract class Sabel_Filter
{
  protected $request = null;
  
  public function setup($request)
  {
    $this->request = $request;
    return $this;
  }
  
  public function execute() {}
  public function input() {}
  public function output(&$responses) {}
}