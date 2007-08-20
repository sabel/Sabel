<?php

class Sabel_Bus_ProcessorCallback
{
  public $name;
  public $method;
  public $when;
  
  public function __construct($processor, $method, $when)
  {
    $this->processor = $processor;
    $this->method = $method;
    $this->when = $when;
  }
}
