<?php

/**
 * Sabel_Aspect_Aspects
 *
 * @category   Aspect
 * @package    org.sabel.aspect
 * @author     Mori Reo <mori.reo@gmail.com>
 * @copyright  2002-2006 Mori Reo <mori.reo@gmail.com>
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
    self::$matcher = new Sabel_Aspect_Matcher();
  }
  
  public static function singleton()
  {
    if (!self::$instance) self::$instance = new self();
    return self::$instance;
  }
  
  public function addPointcut($pointcut)
  {
    self::$matcher->add($pointcut);
    $this->pointcuts[] = $pointcut;
    $className = $pointcut->getName();
    $this->aspects[] = new $className();
  }
  
  public function add($aspect)
  {
    $this->aspects[] = $aspect;
    self::$matcher->add($aspect->pointcut());
  }
  
  public function findMatch($conditions)
  {
    $pointcuts = $this->pointcuts;
    $matches = new Sabel_Aspect_Matches();
    
    $class     = $conditions['class'];
    $method    = $conditions['method'];
    
    foreach ($pointcuts as $p) {
      $match = false;
      switch ($p) {
        case ($p->hasToAll()):
          $match = true;
          break;
        case ($p->hasClass() && $p->hasMethod() &&
              $p->getClass() === $class && $p->getMethod() === $method):
          $match = true;
          break;
        case ($p->hasClass() && $p->getClass() === $class):
          $match = true;
          break;
        case ($p->hasClassRegex() && preg_match('/'.$p->getClassRegex().'/', $class)):
          $match = true;
          break;
        case ($p->hasMethod() && $p->getMethod() === $method):
          $match = true;
          break;
        case ($p->hasMethods()):
          foreach ($p->getMethods() as $pcMethod) {
            
            if ($pcMethod === $method) {
              $matches->add($p->getName(), $p->getAspect());
            }
          }
          break;
        case ($p->hasMethodRegex() && preg_match('/'.$p->getMethodRegex().'/', $method)):
          $match = true;
          break;
      }
      if ($match) $matches->add($p->getName(), $p->getAspect());
    }
    
    return $matches;
  }
  
  public function findExceptionMatch($conditions)
  {
    $pointcuts = $this->pointcuts;
    $matches = new Sabel_Aspect_Matches();
    
    $class = $conditions['class'];
    
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
              && preg_match('/'.$p->getExceptionClassRegex().'/', $class)):
          $match = true;
          break;
      }
      if ($match) $matches->add($p->getName(), $p->getAspect());
    }
    
    return $matches;
  }
}