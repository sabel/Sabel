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
  protected static $instance = null;
  
  /**
   * default constructer
   *
   * @param void
   */
  public function __construct()
  {
  }
  
  public static function create()
  {
    if (self::$instance === null) self::$instance = new self();
    return self::$instance;
  }
  
  public function read($className)
  {
    $ref = new ReflectionClass($className);
    return $this->process($ref->getDocComment());
  }
  
  public function readMethods($className)
  {
    $methods = array();
    $reflection = new ReflectionClass($className);
    
    foreach ($reflection->getMethods() as $method) {
      $methods[$method->getName()] = $this->process($method->getDocComment());
    }
    
    return $methods;
  }
  
  public function process($comment)
  {
    $annotations = array();
    $comments = self::splitComment($comment);
    
    foreach ($comments as $line) {
      list($name, $value) = self::extract($line);
      
      if (isset($annotations[$name])) {
        if (is_string($annotations[$name])) {
          $annotations[$name] = array($annotations[$name]);
        }
        
        $annotations[$name][] = $value;
      } else {
        $annotations[$name] = $value;
      }
    }
    
    return $annotations;
  }
  
  protected static function splitComment($comment)
  {
    return preg_split("/[\r\n]/", $comment, -1, PREG_SPLIT_NO_EMPTY);
  }
  
  protected static function extract($line)
  {
    $annotation = preg_split("/ +/", self::removeComment($line));
    
    if (strpos($annotation[0], "@") === 0) {
      $name  = array_shift($annotation);
      $values = (count($annotation) >= 2) ? $annotation : $annotation[0];
      
      if (is_array($values)) {
        $in = false;
        $buf = "";
        $nval = array();
        foreach ($values as $value) {
          if ($value{0} === '"') {
            $in = true;
            $buf .= substr($value, 1, strlen($value));
          } elseif ($value{strlen($value)-1} === '"') {
            $in = false;
            $buf .= " " . substr($value, 0, strlen($value)-1);
            $nval[] = $buf;
          } elseif (!$in) {
            $nval[] = $value;
          }
        }
        if (count($nval) === 1) $nval = $nval[0];
        return array(ltrim($name, "@ "), $nval);
      } else {
        return array(ltrim($name, "@ "), $values);
      }
    } else {
      return null;
    }
  }
  
  protected static function removeComment($line)
  {
    $line = preg_replace('/^\/?[\s\*]+/', "", trim($line));
    return  preg_replace('/[\s\*]+\/$/',  "", $line);
  }
}
