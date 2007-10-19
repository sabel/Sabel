<?php

/**
 * Sabel_Reflection_Method
 *
 * @category   Reflection
 * @package    org.sabel.reflection
 * @author     Mori Reo <mori.reo@gmail.com>
 * @copyright  2002-2006 Mori Reo <mori.reo@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Reflection_Method extends ReflectionMethod
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
