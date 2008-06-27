<?php

/**
 * TestCase of sabel.aspect.pointcuts
 *
 * @author Mori Reo <mori.reo@sabel.jp>
 */
class Test_Aspect_StaticProxy extends Test_Aspect_Base
{
  public static function suite()
  {
    return self::createSuite("Test_Aspect_StaticProxy");
  }
  
  public function setUp()
  {
    $this->weaver = new Sabel_Aspect_StaticWeaver("Sabel_Tests_Aspect_TargetClass");
  }
  
  public function testWeave()
  {
    $weaver = $this->weaver;
    
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
    $weaver = $this->weaver;
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
    $weaver = $this->weaver;
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
}