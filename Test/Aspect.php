<?php

Sabel::fileUsing("sabel/aspect/Interfaces.php");
Sabel::fileUsing("sabel/aspect/Matchers.php");
Sabel::fileUsing("sabel/aspect/Pointcuts.php");
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
  
  public function TestPointcuts()
  {
    $pointcuts = new Sabel_Aspect_DefaultPointcuts();
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
    
    $pointcut = new Sabel_Aspect_DefaultRegexPointcut();
    $pointcut->setClassMatchPattern("/Sabel+/");
    $pointcut->setMethodMatchPattern("/set+/");
    
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
    
    $interceptor = new Sabel_Aspect_DebugInterceptor();
    $advisor = new MyStaticMethodMatcherPointcutAdvisor();
    $advisor->setAdvice($interceptor);
    
    $weaver->addAdvisor($advisor);
    
    $interceptor = new Sabel_Aspect_SimpleTraceInterceptor();
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
  
  public function testStaticWeave()
  {
    $weaver = new Sabel_Aspect_StaticWeaver();
    $weaver->setTarget("Sabel_Tests_Aspect_TargetClass");
    
    $advisor = new MyRegexMethodMatcherPointcutAdvisor();
    $advisor->setPattern("/fetch+/");
    $advisor->setAdvice(new Sabel_Aspect_SimpleTraceInterceptor());
    
    $weaver->addAdvisor($advisor);
    
    $target = $weaver->getProxy();
    
    // not match
    $this->assertEquals("Sabel_Tests_Aspect_TargetClass", get_class($target));
  }
  
  public function testStaticWeaveMatch()
  {
    $weaver = new Sabel_Aspect_StaticWeaver();
    $weaver->setTarget("Sabel_Tests_Aspect_TargetClass");
    
    $advisor = new Sabel_Aspect_RegexMatcherPointcutAdvisor();
    $advisor->setClassMatchPattern("/*/");
    $advisor->setMethodMatchPattern("/get+/");
    
    $advisor->setAdvice(new Sabel_Aspect_SimpleTraceInterceptor());
    
    $weaver->addAdvisor($advisor);
    
    $target = $weaver->getProxy();
    
    // match
    $this->assertEquals("Sabel_Aspect_StaticProxy", get_class($target));
  }
  
  public function testDynamicMatch()
  {
    $weaver = new Sabel_Aspect_Weaver();
    $weaver->setTarget("Sabel_Tests_Aspect_TargetClass");
    $target = $weaver->getProxy();
    
    $this->assertEquals($target->getClassName(), "Sabel_Tests_Aspect_TargetClass");
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

class MyRegexMethodMatcherPointcutAdvisor extends Sabel_Aspect_StaticMethodMatcherPointcutAdvisor
{
  private $pattern;
  
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
  
  public function setPattern($pattern)
  {
    $this->pattern = $pattern;
  }
  
  public function matches($method, $class)
  {
    return preg_match($this->pattern, $method);
  }
}
