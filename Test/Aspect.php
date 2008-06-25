<?php

Sabel::fileUsing("sabel/aspect/Interfaces.php");
Sabel::fileUsing("sabel/aspect/Matchers.php");
Sabel::fileUsing("sabel/aspect/Advisors.php");

Sabel::fileUsing("sabel/aspect/Interceptors.php");

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
    $pointcuts = new DefaultPointcuts();
    $target = new Sabel_Tests_Aspect_TargetClass();
    $match = $pointcuts->matches(new StaticPointcut(), "setX", $target);
    
    $this->assertTrue($match);
  }
  
  public function testRegexMethodMatcher()
  {
    $matcher = new Sabel_Aspect_RegexMethodMatcher();
    $matcher->setPattern("/set+/");
    $this->assertTrue($matcher->matches("setX", ""));
  }
  
  public function testRegexClassMatcher()
  {
    $matcher = new Sabel_Aspect_RegexClassMatcher();
    $matcher->setPattern("/Sabel_+/");
    $this->assertTrue($matcher->matches("Sabel_Test", ""));
    
    $matcher->setPattern("/Sabel_+/");
    $this->assertFalse($matcher->matches("Test_Test", ""));
  }
  
  public function testRegexMatcherPointcuts()
  {
    $pointcuts = new DefaultPointcuts();
    $target = new Sabel_Tests_Aspect_TargetClass();
    
    $pointcut = new Sabel_Aspect_RegexPointcut();
    $pointcut->setClassPattern("/Sabel+/");
    $pointcut->setMethodPattern("/set+/");
    
    $match = $pointcuts->matches($pointcut, "setX", $target);
    $this->assertTrue($match);
    
    $match = $pointcuts->matches($pointcut, "setY", $target);
    $this->assertTrue($match);
    
    $match = $pointcuts->matches($pointcut, "getY", $target);
    $this->assertFalse($match);
  }
  
  public function testWeaverWithInterceptor()
  {
    $weaver = new Sabel_Aspect_Weaver(new Sabel_Tests_Aspect_TargetClass());
    
    $interceptor = new DebugInterceptor();
    $advisor = new MyStaticMethodMatcherPointcutAdvisor();
    $advisor->setAdvice($interceptor);
    
    $weaver->addAdvisor($advisor);
    
    $interceptor = new SimpleTraceInterceptor();
    $advisor = new MyStaticMethodMatcherPointcutAdvisor();
    $advisor->setAdvice($interceptor);
    
    $weaver->addAdvisor($advisor);
    
    $target = $weaver->getProxy();
    
    $result = $target->getX("arg", "arg2");
    $this->assertEquals("X", $result);
    
    $result = $target->getY("arg", "arg2");
    $this->assertEquals("Y", $result);
  }
  
  public function testAopWeaverWithoutInterceptors()
  {
    $weaver = new Sabel_Aspect_Weaver(new Sabel_Tests_Aspect_TargetClass());
    
    $target = $weaver->getProxy();
    
    $result = $target->getX("arg", "arg2");
    $this->assertEquals("X", $result);
    
    $result = $target->getY("arg", "arg2");
    $this->assertEquals("Y", $result);
  }
}

class DefaultPointcuts extends Sabel_Aspect_Pointcuts
{
}

class Sabel_Tests_Aspect_TargetClass
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
  
  public function getName()
  {
  }
}

class StaticPointcut implements Sabel_Aspect_Pointcut
{
  public function getClassMatcher()
  {
    return new MyStaticClassNameMatcher();
  }
  
  public function getMethodMatcher()
  {
    return new MyMethodMatcher();
  }
}

class MyStaticClassNameMatcher extends Sabel_Aspect_StaticClassNameMatcher
{
  public function matches($class)
  {
    return ($class->getName() === "Sabel_Tests_Aspect_TargetClass");
  }
}

class MyMethodMatcher extends Sabel_Aspect_StaticMethodMatcher
{
  public function matches($method, $class)
  {
    return ($method === "setX");
  }
}

class MyStaticMethodMatcherPointcutAdvisor extends Sabel_Aspect_StaticMethodMatcherPointcutAdvisor
{
  public function __construct()
  {
    if (!class_exists("MyClassMatcher")) {
      eval('
        class MyClassMatcher implements Sabel_Aspect_ClassMatcher {
          public function matches($class)
          {
            return true;
          }
        }
      ');
    }
    
    $this->setClassMatcher(new MyClassMatcher());
  }
  
  public function matches($method, $class)
  {
    return preg_match("/get+/", $method);
  }
}
