<?php

/**
 * Sabel_Aspect_Aspects
 *
 * @category   Aspect
 * @package    org.sabel.aspect
 * @author     Mori Reo <mori.reo@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Aspect_Aspects
{
  protected $aspects   = array();
  protected $pointcuts = array();
  
  protected static $matcher  = null;
  protected static $instance = null;
  
  protected function __construct()
  {
    //self::$matcher = new Sabel_Aspect_Matcher();
  }
  
  public static function singleton()
  {
    if (!self::$instance) self::$instance = new self();
    return self::$instance;
  }
  
  public function addPointcut($pointcut)
  {
    $className = $pointcut->getName();
    
    $this->pointcuts[$className] = $pointcut;
    $this->aspects[$className]   = $pointcut->getAspect();
  }
  
  public function getPointCut($name)
  {
    return $this->aspects[$name];
  }
  
  public function findMatch($conditions)
  {
    $pointcuts = $this->pointcuts;
    $matches   = array();
    
    $class  = $conditions["class"];
    $method = $conditions["method"];
    
    foreach ($pointcuts as $pointcut) {
      $match = false;
      
      if ($pointcut->hasToAll() ||
          ($pointcut->hasMethod() && $pointcut->hasMethod() === $method)) {
        $match = true;
      } elseif ($pointcut->hasMethods()) {
        foreach ($pointcut->getMethods() as $pointcutMethod) {
          if ($pointcutMethod === $method) {
            $matches[$pointcut->getName()] = $pointcut->getAspect();
          }
        }
        $match = true;
      } elseif ($pointcut->hasMethodRegex() &&
                preg_match("/" . $pointcut->getMethodRegex() . "/", $method)) {
        $match = true;
      }
      
      if ($match) $matches[$pointcut->getName()] = $pointcut->getAspect();
    }
    
    return $matches;
  }
  
  public function findExceptionMatch($conditions)
  {
    $pointcuts = $this->pointcuts;
    $matches = array();
    
    $class = $conditions["class"];
    
    foreach ($pointcuts as $p) {
      $match = false;
      if (!$p->hasException()) continue;
      switch ($p) {
        case ($p->hasAnyException()):
          $match = true;
          break;
        case ($p->hasExceptionClass() && $p->getExceptionClass() === $class):
          $match = true;
          break;
        case ($p->hasExceptionClassRegex()
              && preg_match("/".$p->getExceptionClassRegex()."/", $class)):
          $match = true;
          break;
      }
      if ($match) $matches->add($p->getName(), $p->getAspect());
    }
    
    return $matches;
  }
}
