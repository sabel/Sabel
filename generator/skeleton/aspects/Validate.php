<?php

/**
 * primitive interceptor of SabelAspect
 *
 * @category   Aspect
 * @package    org.sabel.aspect.interceptors
 * @author     Mori Reo <mori.reo@gmail.com>
 * @copyright  2002-2006 Mori Reo <mori.reo@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Aspects_Validate
{
  protected static $failureCallbacks = null;
  protected static $successCallbacks = null;
  
  protected static $redirectWhenSuccess = '';
  protected static $redirectWhenFailure = '';
  
  public static function addSuccessCallback($function)
  {
    self::$successCallbacks[] = $function;
  }
  
  public static function addFailureCallback($function)
  {
    self::$failureCallbacks[] = $function;
  }
  
  public static function redirectWhenSuccess($uri)
  {
    self::$redirectWhenSuccess = $uri;
  }
  
  public static function redirectWhenFailure($uri)
  {
    self::$redirectWhenFailure = $uri;
  }
  
  public function around($joinpoint)
  {
    $target    = $joinpoint->getTarget();
    $className = $joinpoint->getReflection()->getName();
    $method    = $joinpoint->getMethodReflection();
    $controller = Sabel_Core_Context::getPageController();
    
    $v = new Sabel_Validate_Model($className);
    $arg = $joinpoint->getArgument(0);
    if (count($arg) === 0) {
      $arg = $target->getValidateData();
    }
    
    $errors = $v->validate($arg);
    
    if ($errors->hasError()) {
      if (self::$redirectWhenFailure !== '') {
        $controller->redirectTo(self::$redirectWhenFailure);
      }
      
      if ($controller->hasErrorMethod()) {
        $errorMethod = $controller->errorMethod();
        $controller->$errorMethod($errors);
      } else {
        Sabel_Template_Engine::setAttribute(strtolower($className), $target);
        Sabel_Template_Engine::setAttribute('errors', $errors);
      }
      
      if (count(self::$failureCallbacks) > 0) {
        foreach (self::$failureCallbacks as $callback) $callback($joinpoint);
      }
      
      return false;
    } else {
      if (count(self::$successCallbacks) > 0) {
        foreach (self::$successCallbacks as $callback) $callback($joinpoint);
      }
      
      $method->invokeArgs($target, $joinpoint->getArguments());
      
      if ($controller->hasSuccessMethod()) {
        $successMethod = $controller->successMethod();
        $controller->$successMethod();
      }
      
      if (self::$redirectWhenSuccess === '') {
        $controller->redirectToPrevious();
      } else {
        $controller->redirectTo(self::$redirectWhenFailure);
      }
      return false;
    }
  }
}

Sabel_Aspect_Aspects::singleton()->addPointcut(
  Sabel_Aspect_Pointcut::create('Aspects_Validate')->setMethod('save')
);