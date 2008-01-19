<?php

/**
 * Sabel_Annotation_Reader
 *
 * @category   Annotation
 * @package    org.sabel.annotation
 * @author     Mori Reo <mori.reo@gmail.com>
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Mori Reo <mori.reo@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Annotation_Reader extends Sabel_Object
{
  protected static $instance = null;
  
  public static function create()
  {
    if (self::$instance === null) {
      self::$instance = new self();
    }
    
    return self::$instance;
  }
  
  public function read($class)
  {
    $reflection = new Sabel_Reflection_Class($class);
    return $this->process($reflection->getDocComment());
  }
  
  public function readMethods($className)
  {
    $methods = array();
    $reflection = new Sabel_Reflection_Class($className);
    
    foreach ($reflection->getMethods() as $method) {
      $methods[$method->getName()] = $this->process($method->getDocComment());
    }
    
    return $methods;
  }
  
  public function process($comment)
  {
    $annotations = array();
    preg_match_all("/@(.+)/", $comment, $comments);
    if (empty($comments[1])) return $annotations;
    
    foreach ($comments[1] as $line) {
      list ($name, $values) = $this->extract($line);
      $annotations[$name][] = $values;
    }
    
    return $annotations;
  }
  
  protected function extract($line)
  {
    if (($pos = strpos($line, " ")) === false) {
      return array($line, null);
    }
    
    $key = substr($line, 0, $pos);
    $values = ltrim(substr($line, $pos));
    
    $regex = '/"([^"]+)"|\'([^\']+)\'|([^ ]+)/';
    preg_match_all($regex, $values, $matches);
    
    $annotValues = array();
    foreach ($matches as $index => $match) {
      if ($index === 0) continue;
      foreach ($match as $k => $v) {
        if ($v !== "") $annotValues[$k] = $v;
      }
    }
    
    return array($key, $annotValues);
  }
}
