<?php

class Sabel_Annotation_ReflectionMethod extends ReflectionMethod
{
  private $annotations = array();
  
  public function __construct($class_or_method, $name = "")
  {
    parent::__construct($class_or_method, $name);
    $reader = new Sabel_Annotation_Reader();
    $this->annotations = $reader->process($this->getDocComment());
  }
  
  public function getAnnotation($name)
  {
    if ($this->hasAnnotation($name)) {
      return $this->annotations[$name];
    } else {
      return null;
    }
  }
  
  public function hasAnnotation($name)
  {
    return (isset($this->annotations[$name]));
  }
  
  public function getAnnotations()
  {
    return $this->annotations;
  }
}
