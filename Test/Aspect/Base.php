<?php

/**
 * TestCase of sabel.aspect.*
 *
 * @author Mori Reo <mori.reo@sabel.jp>
 */
class Test_Aspect_Base extends SabelTestCase
{
  protected $weaver = null;
  
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
  
  public function testWeaverWithInterceptor()
  {
    $weaver = $this->weaver;
    
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
    $weaver = $this->weaver;
    
    $target = $weaver->getProxy();
    
    $result = $target->getX("arg", "arg2");
    $this->assertEquals("X", $result);
    
    $result = $target->getY("arg", "arg2");
    $this->assertEquals("Y", $result);
  }
  
  public function testMultipleAdvice()
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
    
    $weaver = $this->weaver;
    
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
  
  public function testSimpleBeforeAdvice()
  {
    $weaver = $this->weaver;
    
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
    $weaver = $this->weaver;
    
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
    $weaver = $this->weaver;
    
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
    $weaver = $this->weaver;
    
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