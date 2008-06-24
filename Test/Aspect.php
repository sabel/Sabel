<?php

/**
 * TestCase of sabel.aspect.*
 *
 * @author Mori Reo <mori.reo@sabel.jp>
 */
class Test_Aspect extends SabelTestCase
{
  public static function suite()
  {
    return self::createSuite("Test_Aspect");
  }
  
  public function testPointcuts()
  {
    $pointcuts = new Pointcuts();
    $target = new Target();
    $match = $pointcuts->matches(new StaticPointcut(), "setX", $target);
    
    $this->assertTrue($match);
  }
  
  public function testAOP()
  {
    $weaver = new Weaver(new Target());
    
    $interceptor = new DebugInterceptor();
    
    $advisor = new StaticMethodMatcherPointcutAdvisor();
    $advisor->setMethod("setX");
    $advisor->setAdvice($interceptor);
    
    $weaver->addAdvisor($advisor);
    
    $target = $weaver->getProxy();
    
    $target->getX();
  }
}

class Target
{
  public function getX()
  {
    return "X";
  }
  
  public function setX($arg)
  {
    return $arg;
  }
  
  public function setY()
  {
    
  }
  
  public function getY()
  {
    return "Y";
  }
}

class StaticMethodMatcherPointcutAdvisor implements PointcutAdvisor
{
  private $method = "";
  
  private $advice = null;
  
  public function setAdvice(Advice $interceptor)
  {
    $this->advice = $interceptor;
  }
  
  public function setMethod($method)
  {
    $this->method = $method;
  }
  
  public function getPointcut()
  {
    
  }
}

class Weaver
{
  private $target = null;
  
  private $advisor = array();
  
  public function __construct($target)
  {
    $this->target = $target;
  }
  
  public function addAdvisor($advisor)
  {
    $this->advisor[] = $advisor;
  }
  
  public function getProxy()
  {
    return new Proxy($this->target);
  }
}

class Proxy
{
  private $target = null;
  
  private $aspects = array();
  
  public function __construct($targetObject)
  {
    $this->target = $targetObject;
  }
  
  public function __call($method, $arg)
  {
    $reflection = new Sabel_Reflection_Class($this->target);
    $reflection->getMethod($method)->invokeArgs($this->target, $arg);
  }
}

interface Joinpoint
{
  public function getStaticPart();
  public function getThis();
  public function proceed();
}

interface Interceptor extends Advice
{
}

interface Invocation extends Joinpoint
{
  public function getArguments();
}

interface MethodInvocation extends Invocation
{
  public function getMethod();
}

interface MethodInterceptor extends Interceptor
{
  public function invoke(MethodInvocation $invocation);
}

class DebugInterceptor implements MethodInterceptor
{
  public function invoke(MethodInvocation $invocation)
  {
    $invocation->proceed();
  }
}

interface Pointcut
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

class Pointcuts
{
  public function matches(Pointcut $pointcut, $method, $class)
  {
    $reflection = new Sabel_Reflection_Class($class);
    
    $classMatcher  = $pointcut->getClassMatcher();
    $methodMatcher = $pointcut->getMethodMatcher();
    
    $classMatch  = $classMatcher->matches($reflection);
    $methodMatch = $methodMatcher->matches($method, $reflection);
    
    return ($classMatch && $methodMatch);
  }
}

class StaticPointcut implements Pointcut
{
  public function getClassMatcher()
  {
    return new StaticClassNameMatcher();
  }
  
  public function getMethodMatcher()
  {
    return new StaticMethodMatcher();
  }
}

class StaticClassNameMatcher implements ClassMatcher
{
  public function matches($class)
  {
    return ($class->getName() === "Target");
  }
}

class StaticMethodMatcher implements MethodMatcher
{
  public function matches($method, $class)
  {
    return ($method === "setX");
  }
}

class StaticMethodMatcherPointcut extends StaticMethodMatcher
{
  
}

interface ClassMatcher
{
  public function matches($class);
}

interface MethodMatcher
{
  public function matches($method, $class);
}

interface Advice
{
  
}

interface Advisor
{
  public function getAdvice(Advice $interceptor);
  public function isPerInstance();
}

interface PointcutAdvisor
{
  public function getPointcut();
}

interface AroundAdvice extends Advice
{
  
}

interface BeforeAdvice extends Advice
{
  
}

interface AfterAdvice extends Advice
{
  
}

interface ThrowsAdvice extends Advice
{
  
}