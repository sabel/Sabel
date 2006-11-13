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
  public function before($joinpoint)
  {
    $target    = $joinpoint->getTarget();
    $className = $joinpoint->getReflection()->getName();
    $method    = $joinpoint->getMethodReflection();
    
    $v = new Sabel_Validate_Model($target);
    $arg = $joinpoint->getArgument(0);
    if (count($arg) === 0) {
      $arg = $target->getValidateData();
    }
    $errors = $v->validate($arg);
    if ($errors->hasError()) {
      Sabel_Template_Engine::setAttribute(strtolower($className), $target);
      Sabel_Template_Engine::setAttribute('errors', $errors);
      return false;
    } else {
      $method->invokeArgs($target, $joinpoint->getArguments());
      Sabel_Core_Context::getPageController()->redirectToPrevious();
      return false;
    }
  }
}

Sabel_Aspect_Aspects::singleton()->addPointcut(
  Sabel_Aspect_Pointcut::create('Aspects_Validate')->setMethod('save')
);