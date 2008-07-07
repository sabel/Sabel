<?php

/**
 * Interfaces
 *
 * @category   aspect
 * @package    org.sabel.aspect
 * @author     Mori Reo <mori.reo@sabel.jp>
 * @copyright  2008-2011 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */

/**
 * @see org.aopalliance.aop.Advice
 */
interface Sabel_Aspect_Advice
{
}

/**
 * @see org.aopalliance.aop.Joinpoint
 */
interface Sabel_Aspect_Joinpoint
{
  public function getStaticPart();
  public function getThis();
  public function proceed();
}

/**
 * @see org.aopalliance.aop.Invocation
 */
interface Sabel_Aspect_Invocation extends Sabel_Aspect_Joinpoint
{
  /**
   * @return array
   */
  public function getArguments();
}

/**
 * @see org.aopalliance.aop.MethodInvocation
 */
interface Sabel_Aspect_MethodInvocation extends Sabel_Aspect_Invocation
{
  /**
   * @return ReflectionMethod
   */
  public function getMethod();
}

/**
 * @see org.aopalliance.aop.Interceptor
 */
interface Sabel_Aspect_Interceptor extends Sabel_Aspect_Advice
{
}

interface Sabel_Aspect_BeforeAdvice extends Sabel_Aspect_Advice
{
}

interface Sabel_Aspect_AfterAdvice extends Sabel_Aspect_Advice
{
}

interface Sabel_Aspect_ThrowsAdvice extends Sabel_Aspect_Advice
{
}

/**
 * @see org.aopalliance.aop.MethodInterceptor
 */
interface Sabel_Aspect_MethodInterceptor extends Sabel_Aspect_Interceptor
{
  public function invoke(Sabel_Aspect_MethodInvocation $invocation);
}

interface Sabel_Aspect_Advisor
{
  public function getAdvice();
  public function isPerInstance();
}

interface Sabel_Aspect_MethodBeforeAdvice extends Sabel_Aspect_BeforeAdvice
{
  public function before($method, $arguments, $target);
}

interface Sabel_Aspect_MethodAfterAdvice extends Sabel_Aspect_AfterAdvice
{
  public function after($method, $arguments, $target, $returnValue);
}

interface Sabel_Aspect_MethodAfterReturningAdvice extends Sabel_Aspect_AfterAdvice
{
  public function after($method, $arguments, $target, $returnValue);
}

interface Sabel_Aspect_MethodThrowsAdvice extends Sabel_Aspect_ThrowsAdvice
{
  public function throws($method, $arguments, $target, $exception);
}

interface Sabel_Aspect_PointcutAdvisor extends Sabel_Aspect_Advisor
{
  public function getPointcut();
}

interface Sabel_Aspect_ClassMatcher
{
  public function matches($class);
}

interface Sabel_Aspect_MethodMatcher
{
  public function matches($method, $class);
}


interface Sabel_Aspect_RegexMatcher
{
  public function setPattern($pattern);
}

/**
 * pointcut interface
 */
interface Sabel_Aspect_Pointcut
{
  /**
   * @return ClassMatcher
   */
  public function getClassMatcher();
  
  /**
   * @return MethodMatcher
   */
  public function getMethodMatcher();
}

interface Sabel_Aspect_RegexPointcut extends Sabel_Aspect_Pointcut
{
  public function setClassMatchPattern($pattern);
  public function setMethodMatchPattern($pattern);
}

abstract class Sabel_Aspect_Pointcuts
{
  public function matches(Sabel_Aspect_Pointcut $pointcut, $method, $class)
  {
    $class = new Sabel_Reflection_Class($class);
    
    if ($pointcut === null) throw new Sabel_Exception_Runtime("pointcut can't be null");
    
    if ($pointcut->getClassMatcher()->matches($class)) {
      return $pointcut->getMethodMatcher()->matches($method, $class);
    }
    
    return false;
  }
}

abstract class Sabel_Aspect_AbstractPointcut implements Sabel_Aspect_Pointcut
{
  protected $classMatcher  = null;
  protected $methodMatcher = null;
  
  public function setClassMatcher(Sabel_Aspect_ClassMatcher $matcher)
  {
    $this->classMatcher = $matcher;
  }
  
  public function setMethodMatcher(Sabel_Aspect_MethodMatcher $matcher)
  {
    $this->methodMatcher = $matcher;
  }
}

abstract class Sabel_Aspect_AbstractProxy
{
  protected $target = null;
  
  protected $advisor = array();
  
  protected $invocation = null;
  
  public function __construct($targetObject)
  {
    $this->target = $targetObject;
    $this->__setupInvocation();
    
    if (!$this->invocation instanceof Sabel_Aspect_MethodInvocation) {
      throw new Sabel_Exception_Runtime("invocation must be setup");
    }
  }
  
  abstract protected function __setupInvocation();
  
  public function __getTarget()
  {
    return $this->target;
  }
  
  public function __setAdvisor($advisor)
  {
    $this->advisor = $advisor;
  }
  
  public function getClassName()
  {
    return get_class($this->target);
  }
}

interface Sabel_Aspect_Weaver
{
  public function getProxy();
}

abstract class Sabel_Aspect_AbstractWeaver implements Sabel_Aspect_Weaver
{
  protected $target  = null;
  protected $advisor = array();
  
  public function __construct($target = null)
  {
    if ($target !== null) {
      $this->target = $target;  
    }
  }
  
  public function addAdvisor($advisor, $position = null)
  {
    if ($position === null) {
      $position = count($this->advisor);
    }
    
    $this->advisor[$position] = $advisor;
  }
  
  /**
   * @param object $target
   */
  public function setTarget($target)
  {
    if (class_exists($target)) {
      $this->target = $target;  
    } else {
      throw new Sabel_Exception_Runtime("target must be exist class. {$target} not found");
    }
  }
}