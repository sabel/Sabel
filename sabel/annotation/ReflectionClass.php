<?php

/**
 * Sabel_Annotation_ReflectionClass
 *
 * @category   Annotation
 * @package    org.sabel.annotation
 * @author     Mori Reo <mori.reo@gmail.com>
 * @copyright  2002-2006 Mori Reo <mori.reo@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Annotation_ReflectionClass extends ReflectionClass
{
  private $annotations = array();
  
  public function __construct($class)
  {
    parent::__construct($class);
    $reader = new Sabel_Annotation_Reader();
    $this->annotations = $reader->read($class);
  }
  
  public function getAnnotation($name)
  {
    if ($this->hasAnnotation($name)) {
      return $this->annotations[$name];
    } else {
      return null;
    }
  }
  
  public function getAnnotations()
  {
    return $this->annotations;
  }
  
  public function hasAnnotation($name)
  {
    return (isset($this->annotations[$name]));
  }
  
  /**
   * overwrite parent getMethods()
   * 
   * @param string $filter optional
   */
  public function getMethods($filter = null)
  {
    $className  = $this->getName();
    $rawMethods = parent::getMethods();
    $methods = array();
    
    foreach ($rawMethods as $method) {
      $name = $method->getName();
      $methods[] = new Sabel_Annotation_ReflectionMethod($className, $name);
    }
    
    return $methods;
  }
  
  public function getMethodsAsAssoc()
  {
    $className = $this->getName();
    $rawMethods = parent::getMethods();
    $methods = array();
    
    foreach ($rawMethods as $method) {
      $name = $method->getName();
      $methods[$name] = new Sabel_Annotation_ReflectionMethod($className, $name);
    }
    
    return $methods;
  }
  
  public function getMethod($name)
  {
    return new Sabel_Annotation_ReflectionMethod($this->getName(), $name);
  }
}
