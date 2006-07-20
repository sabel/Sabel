<?php

class Sabel_Annotation_Context
{
  protected $name;
  protected $contents;
  
  public function __construct($name, $contents)
  {
    $this->name     = $name;
    $this->contents = $contents;
  }
  
  /**
   * get an annotation name
   *
   * @param void
   * @return string $this->name;
   */
  public function getName()
  {
    return $this->name;
  }
  
  public function getContents()
  {
    return $this->contents;
  }
  
  protected function isInjection()
  {
    return ($this->getName() === 'injection');
  }
  
  public function createInjection()
  {
    if ($this->isInjection()) {
      $className = $this->getContents();
      return new $className();
    }
  }
}