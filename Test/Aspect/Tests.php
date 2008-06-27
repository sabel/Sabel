<?php

Sabel::fileUsing("sabel/aspect/Interfaces.php");
Sabel::fileUsing("sabel/aspect/Matchers.php");
Sabel::fileUsing("sabel/aspect/Pointcuts.php");
Sabel::fileUsing("sabel/aspect/Advisors.php");

Sabel::fileUsing("sabel/aspect/Interceptors.php");

require_once ("Test/Aspect/Base.php");
require_once ("Test/Aspect/DynamicProxy.php");
require_once ("Test/Aspect/StaticProxy.php");
require_once ("Test/Aspect/Pointcuts.php");
require_once ("Test/Aspect/Matcher.php");

/**
 *
 * @category  Aspect
 * @author    Mori Reo <mori.reo@sabel.jp>
 */
class Test_Aspect_Tests
{
  public static function main()
  {
    PHPUnit_TextUI_TestRunner::run(self::suite());
  }
  
  public static function suite()
  {
    $suite = new PHPUnit_Framework_TestSuite();
    
    $suite->addTest(Test_Aspect_DynamicProxy::suite());
    $suite->addTest(Test_Aspect_StaticProxy::suite());
    $suite->addTest(Test_Aspect_Pointcuts::suite());
    $suite->addTest(Test_Aspect_Matcher::suite());
    
    return $suite;
  }
}