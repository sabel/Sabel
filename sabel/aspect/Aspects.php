<?php

class Sabel_Aspect_Aspects
{
  protected $aspects = array();
  
  protected static $matcher  = null;
  protected static $instance = null;
  
  protected function __construct()
  {
    self::$matcher = new Sabel_Aspect_Matcher();
  }
  
  public static function singleton()
  {
    if (!self::$instance) self::$instance = new self();
    return self::$instance;
  }
  
  public function add($aspect)
  {
    $this->aspects[] = $aspect;
    self::$matcher->add($aspect->pointcut());
  }
  
  public function findMatch($conditions)
  {
    return self::$matcher->findMatch($conditions);
  }
}