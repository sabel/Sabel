<?php

uses('sabel.annotation.Context');
uses('sabel.library.ArrayList');

class Sabel_Annotation_Reader
{
  protected $list = null;
  protected static $annotation = array();
  
  /**
   * default constructer
   *
   * @param void
   */
  public function __construct()
  {
    $this->list = new Sabel_Library_ArrayList();
  }
  
  public function annotation($className)
  {
    if (array_key_exists($className, self::$annotation)) {
      return self::$annotation[$className];
    } else {
      $ref = new ReflectionClass($className);
      foreach ($ref->getMethods() as $method) {
        $this->processMethod($method->getDocComment());
      }
      self::$annotation[$className] = $this->list;
      
      return $this->list;
    }
  }
  
  protected function processMethod($comment)
  {
    $comments = preg_split("/[\n\r]/", $comment, -1, PREG_SPLIT_NO_EMPTY);
    foreach ($comments as $line) {
      $this->processAnnotation($line);
    }
  }
  
  protected function processAnnotation($line)
  {
    $annotation = preg_split('/ +/', $this->removeComment($line));
    
    if ($annotation[0]{0} === '@') {
      $name       = array_shift($annotation);
      $annotation = (count($annotation) > 2) ? $annotation : $annotation[0];
      
      $this->list->push(new Sabel_Annotation_Context(ltrim($name, '@ '), $annotation));
    }
  }
  
  protected function removeComment($line)
  {
    $line =     preg_replace('/^\*/',     '', trim($line));
    $line =     preg_replace('/\*\/$/',   '',      $line);
    return trim(preg_replace('/^\/\*\*/', '',      $line));
  }
}