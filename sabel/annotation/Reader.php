<?php

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
    if (!array_key_exists($className, self::$annotation)) {
      $ref = new ReflectionClass($className);
      foreach ($ref->getMethods() as $method) {
        $this->processMethod($method->getDocComment());
      }
      self::$annotation[$className] = $this->list;
    }
    return self::$annotation[$className];
  }
  
  protected function processMethod($comment)
  {
    $comments = preg_split("/[\n\r]/", $comment, -1, PREG_SPLIT_NO_EMPTY);
    foreach ($comments as $line) {
      $this->list->push(Sabel_Annotation_Utility::processAnnotation($line));
    }
  }
  
  public static function getAnnotations($comment)
  {
    $annotations = array();
    $comments = preg_split("/[\n\r]/", $comment, -1, PREG_SPLIT_NO_EMPTY);
    foreach ($comments as $line) {
      $annotations[] = Sabel_Annotation_Utility::processAnnotation($line);
    }
    return $annotations;
  }
  
  public static function getAnnotationsByProperty($property)
  {
    $annotations = array();
    $rawComment = $property->getDocComment();
    
    $as = self::getAnnotations($rawComment);
    foreach ($as as $annotation) {
      if (is_object($annotation)) {
        $annotations[$property->getName()][$annotation->getName()] = $annotation;
      }
    }
    
    return $annotations;
  }
}