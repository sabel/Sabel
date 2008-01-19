<?php

/**
 * Sabel_Reflection_Class
 *
 * @category   Reflection
 * @package    org.sabel.reflection
 * @author     Mori Reo <mori.reo@gmail.com>
 * @copyright  2002-2006 Mori Reo <mori.reo@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Reflection_Class extends ReflectionClass
{
  protected $annotations = false;
  
  public function getAnnotation($name)
  {
    $annotations = $this->getAnnotations();
    return ($annotations[$name]) ? $annotations[$name] : null;
  }
  
  public function getAnnotations()
  {
    if ($this->annotations === false) {
      $reader = new Sabel_Annotation_Reader();
      $this->annotations = $reader->read($this->getName());
    }
    
    return $this->annotations;
  }
  
  public function hasAnnotation($name)
  {
    $annotations = $this->getAnnotations();
    return isset($annotations[$name]);
  }

  public function getMethodAnnotation($name, $annotationName)
  {
    return $this->getMethod($name)->getAnnotation($annotationName);
  }
  
  public function getMethod($name)
  {
    return new Sabel_Reflection_Method($this->getName(), $name);
  }
}
