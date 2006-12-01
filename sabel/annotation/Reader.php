<?php

Sabel::using('Sabel_Annotation_Context');
Sabel::using('Sabel_Library_ArrayList');

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
  protected $path = '';
  
  protected static $annotation = array();
  protected static $instance = null;
  
  /**
   * default constructer
   *
   * @param void
   */
  public function __construct()
  {
    $this->path = $path = RUN_BASE . '/cache/annotation.cache';
    
    if (is_readable($path)) {
      self::$annotation = unserialize(file_get_contents($path));
    }
    
    $this->list = Sabel::load('Sabel_Library_ArrayList');
  }
  
  public static function create()
  {
    if (self::$instance === null) self::$instance = new self();
    return self::$instance;
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
      if (is_writable($this->path)) {
        file_put_contents($this->path, serialize(self::$annotation));
      } elseif (($fp = fopen($this->path, 'w+'))) {
        fwrite($fp, serialize(self::$annotation));
        fclose($fp);
      } else {
        throw new Sabel_Exception_Runtime($this->path . "can't open");
      }
    }
    return self::$annotation[$className];
  }
  
  public function getAnnotationsByName($className, $name)
  {
    $annotations = array();
    $annos = self::$annotation[$className]->toArray();
    
    foreach ($annos as $annot) {
      if (isset($annos[$name])) $annotations[] = $annot;
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
      if ($annot) {
        $this->list->push($annot);
        $this->list->set($annot->getName(), $annot);
      }
    }
  }
  
  protected static function splitComment($comment)
  {
    return preg_split("/[\r\n]/", $comment, -1, PREG_SPLIT_NO_EMPTY);
  }
}
