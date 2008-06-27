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
  
  private $weavers = array();
  
  public function setUp()
  {
    $this->weavers[] = new Sabel_Aspect_StaticWeaver("Sabel_Tests_Aspect_TargetClass");
    $this->weavers[] = new Sabel_Aspect_DynamicWeaver("Sabel_Tests_Aspect_TargetClass2");
  }
  
  public function testPointcuts()
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
    $weaver = new Sabel_Aspect_DynamicWeaver(new Sabel_Tests_Aspect_TargetClass());
    
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
    $weaver = new Sabel_Aspect_DynamicWeaver(new Sabel_Tests_Aspect_TargetClass());
    
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
    $advisor->setClassMatchPattern("/.+/U");
    $advisor->setMethodMatchPattern("/get+/");
    $advisor->addAdvice(new Sabel_Aspect_SimpleTraceInterceptor());
    
    $weaver->addAdvisor($advisor);
    
    $target = $weaver->getProxy();
    
    // match
    $this->assertEquals("Sabel_Aspect_StaticProxy", get_class($target));
  }
  
  public function testStaticWeaveNotMatch()
  {
    $weaver = new Sabel_Aspect_StaticWeaver();
    $weaver->setTarget("Sabel_Tests_Aspect_TargetClass");
    
    $advisor = new Sabel_Aspect_RegexMatcherPointcutAdvisor();
    $advisor->setClassMatchPattern("/.+/U");
    $advisor->setMethodMatchPattern("/fetch+/");
    
    $target = $weaver->getProxy();
    
    // not match
    $this->assertEquals("Sabel_Tests_Aspect_TargetClass", get_class($target));
    
    $result = $target->getX();
    $this->assertEquals("X", $result);
  }
  
  public function testSiwtchProxy()
  {
    defineClass("ResultInterceptor", '
      class %s implements Sabel_Aspect_MethodInterceptor
      {
        public function invoke(Sabel_Aspect_MethodInvocation $invocation)
        {
          return "adviced " . $invocation->proceed();
        }
      }
    ');
    
    $this->innerTestMultipleAdvice(new Sabel_Aspect_StaticWeaver());
    $this->innerTestMultipleAdvice(new Sabel_Aspect_DynamicWeaver());
  }
  
  private function innerTestMultipleAdvice($weaver)
  {
    $weaver->setTarget("Sabel_Tests_Aspect_TargetClass");
    
    $advisor = new Sabel_Aspect_RegexMatcherPointcutAdvisor();
    $advisor->setClassMatchPattern("/.+/U");
    $advisor->setMethodMatchPattern("/get+/");
    
    $advisor->addAdvice(new ResultInterceptor());
    
    $weaver->addAdvisor($advisor);
    
    $target = $weaver->getProxy();
    
    $this->assertEquals("adviced X", $target->getX());
    $this->assertEquals("adviced Y", $target->getY());
    
    $advisor->addAdvice(new ResultInterceptor());
    
    $this->assertEquals("adviced adviced X", $weaver->getProxy()->getX());
    $this->assertEquals("adviced adviced Y", $weaver->getProxy()->getY());
    
    $advisor->addAdvice(new ResultInterceptor());
    
    $this->assertEquals("adviced adviced adviced X", $weaver->getProxy()->getX());
    $this->assertEquals("adviced adviced adviced Y", $weaver->getProxy()->getY());
  }
  
  public function testDynamicWeaveGetClassName()
  {
    $weaver = new Sabel_Aspect_DynamicWeaver();
    $weaver->setTarget("Sabel_Tests_Aspect_TargetClass");
    $target = $weaver->getProxy();
    
    $this->assertEquals($target->getClassName(), "Sabel_Tests_Aspect_TargetClass");
  }
  
  public function testDynamicWeaveClass()
  {
    $weaver = new Sabel_Aspect_DynamicWeaver();
    $weaver->setTarget("Sabel_Tests_Aspect_TargetClass");
    $target = $weaver->getProxy();
    
    $this->assertEquals(get_class($target), "Sabel_Aspect_DefaultProxy");
  }
  
  public function testSimpleBeforeAdvice()
  {
    $weaver = new Sabel_Aspect_StaticWeaver("Sabel_Tests_Aspect_TargetClass2");
    $advisor = new Sabel_Aspect_RegexMatcherPointcutAdvisor();
    $advisor->setClassMatchPattern("/.+/U");
    $advisor->setMethodMatchPattern("/get+/");
    
    $beforeAdvice = new Sabel_Tests_Aspect_SimpleBeforeAdvice();
    $advisor->addAdvice(new Sabel_Tests_Aspect_SimpleAfterReturningAdvice());
    $advisor->addAdvice(new Sabel_Aspect_SimpleTraceInterceptor());
    $advisor->addAdvice($beforeAdvice);
    
    $weaver->addAdvisor($advisor);
    
    $target = $weaver->getProxy();
    
    $target->getX();
    $target->getY();
    
    $this->assertEquals(array("getX", "getY"), $beforeAdvice->getCalledMethods());
  }
  
  public function testSimpleAfterAdvice()
  {
    $weaver = new Sabel_Aspect_StaticWeaver("Sabel_Tests_Aspect_TargetClass2");
    $advisor = new Sabel_Aspect_RegexMatcherPointcutAdvisor();
    $advisor->setClassMatchPattern("/.+/U");
    $advisor->setMethodMatchPattern("/get+/");
    
    $advice = new Sabel_Tests_Aspect_SimpleAfterReturningAdvice();
    $advisor->addAdvice($advice);
    
    $weaver->addAdvisor($advisor);
    
    $target = $weaver->getProxy();
    
    $target->getX();
    $target->getY();
    
    $this->assertEquals(array("X", "Y"), $advice->getResults());
  }
  
  public function testSimpleThrowsAdvice()
  {
    $weaver = new Sabel_Aspect_StaticWeaver("Sabel_Tests_Aspect_TargetClass2");
    $advisor = new Sabel_Aspect_RegexMatcherPointcutAdvisor();
    $advisor->setClassMatchPattern("/.+/U");
    $advisor->setMethodMatchPattern("/willThrowException/");
    
    $advice = new Sabel_Tests_Aspect_SimpleThrowsAdvice();
    $advisor->addAdvice($advice);
    
    $weaver->addAdvisor($advisor);
    
    $target = $weaver->getProxy();
    
    $this->assertEquals("", $advice->getThrowsMessage());
    
    $target->willThrowException();
    
    $this->assertEquals("throws", $advice->getThrowsMessage());
  }
  
  public function testSimpleThrowsAndReturnAdvice()
  {
    $weaver  = new Sabel_Aspect_StaticWeaver("Sabel_Tests_Aspect_TargetClass2");
    $advisor = new Sabel_Aspect_RegexMatcherPointcutAdvisor();
    $advisor->setClassMatchPattern("/.+/U");
    $advisor->setMethodMatchPattern("/.+/");
    
    $throwsAdvice    = new Sabel_Tests_Aspect_SimpleThrowsAdvice();
    $beforeAdvice    = new Sabel_Tests_Aspect_SimpleBeforeAdvice();
    $returningAdvice = new Sabel_Tests_Aspect_SimpleAfterReturningAdvice();
    
    $advisor->addAdvice($throwsAdvice);
    $advisor->addAdvice($returningAdvice);
    $advisor->addAdvice($beforeAdvice);
    
    $weaver->addAdvisor($advisor);
    
    $target = $weaver->getProxy();
    
    $this->assertEquals("", $throwsAdvice->getThrowsMessage());
    
    $target->willThrowException();
    
    $this->assertEquals("throws", $throwsAdvice->getThrowsMessage());
    $this->assertEquals(array(), $returningAdvice->getResults());
    $this->assertEquals(array("willThrowException"), $beforeAdvice->getCalledMethods());
  }
  
  public function testAdvices()
  {
    $advices = new Sabel_Aspect_Advices();
    $advices->addAdvice(new Sabel_Aspect_SimpleTraceInterceptor());
    $advices->addAdvice(new Sabel_Tests_Aspect_SimpleBeforeAdvice());
    $advices->addAdvice(new Sabel_Tests_Aspect_SimpleAfterReturningAdvice());
    
    foreach ($advices->toArray() as $advice) {
      $this->assertTrue($advice instanceof Sabel_Aspect_Advice);
    }
  }
  
  public function testSimpleIntroduce()
  {
  }
}

class Sabel_Tests_Aspect_SimpleBeforeAdvice implements Sabel_Aspect_MethodBeforeAdvice
{
  private $calledMethods = array();
  
  public function before($method, $arguments, $target)
  {
    $this->calledMethods[] = $method->getName();
  }
  
  public function getCalledMethods()
  {
    return $this->calledMethods;
  }
}

class Sabel_Tests_Aspect_SimpleAfterReturningAdvice implements Sabel_Aspect_MethodAfterReturningAdvice
{
  private $results = array();
  
  public function after($method, $arguments, $target, $returnValue)
  {
    $this->results[] = $returnValue;
  }
  
  public function getResults()
  {
    return $this->results;
  }
}

class Sabel_Tests_Aspect_SimpleThrowsAdvice implements Sabel_Aspect_MethodThrowsAdvice
{
  private $throwsMessage = "";
  
  public function throws($method, $arguments, $target, $exception)
  {
    $this->throwsMessage = $exception->getMessage();
  }
  
  public function getThrowsMessage()
  {
    return $this->throwsMessage;
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
  
  public function willThrowException()
  {
    throw new Sabel_Exception_Runtime("throws");
  }
}

class Sabel_Tests_Aspect_TargetClass2 extends Sabel_Tests_Aspect_TargetClass
{
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
    defineClass("MyClassMatcher", '
      class %s implements Sabel_Aspect_ClassMatcher
      {
        public function matches($class)
        {
          return true;
        }
      }
    ');
    
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
    defineClass("MyClassMatcher", '
      class %s implements Sabel_Aspect_ClassMatcher
      {
        public function matches($class)
        {
          return true;
        }
      }
    ');
    
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

function defineClass($className, $class)
{
  if (!class_exists($className)) {
    eval(sprintf($class, $className));
  }
}
