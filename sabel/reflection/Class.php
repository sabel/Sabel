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
  private $source = null;
  
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
  
  public function getMethod($name)
  {
    return new Sabel_Reflection_Method($this->getName(), $name);
  }
}
