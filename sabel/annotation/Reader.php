<?php

/**
 * Sabel_Annotation_Reader
 *
 * @category   Annotation
 * @package    org.sabel.annotation
 * @author     Mori Reo <mori.reo@gmail.com>
 * @copyright  2002-2006 Mori Reo <mori.reo@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
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
    if (!isset(self::$annotation[$className])) {
      $ref = new ReflectionClass($className);
      $this->process($ref->getDocComment());
      foreach ($ref->getMethods() as $method) {
        $this->process($method->getDocComment());
      }
      self::$annotation[$className] = $this->list;
    }
    return self::$annotation[$className];
  }
  
  public function getAnnotationsByName($className, $name)
  {
    $annotations = array();
    foreach (self::$annotation[$className] as $annot) {
      if ($annot->getName() === $name) $annotations[] = $annot;
    }
    return $annotations;
  }
  
  public static function getAnnotations($comment)
  {
    $annotations = array();
    foreach (self::splitComment($comment) as $line) {
      $annot = Sabel_Annotation_Utility::processAnnotation($line);
      if ($annot) $annotations[] = $annot;
    }
    return $annotations;
  }
  
  public static function getAnnotationsByProperty($property)
  {
    $rawComment  = $property->getDocComment();
    $annotations = array();
    foreach (self::getAnnotations($rawComment) as $annotation) {
      $annotations[$property->getName()][$annotation->getName()] = $annotation;
    }
    return $annotations;
  }
  
  protected function process($comment)
  {
    foreach (self::splitComment($comment) as $line) {
      $annot = Sabel_Annotation_Utility::processAnnotation($line);
      if ($annot) $this->list->push($annot);
    }
  }
  
  protected static function splitComment($comment)
  {
    return preg_split("/[\r\n]/", $comment, -1, PREG_SPLIT_NO_EMPTY);
  }
}
