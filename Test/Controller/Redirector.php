<?php

/**
 * test for sabel.controller.Redirector
 * using sabel.map and sabel.Context
 *
 * @category Controller
 * @author   Ebine Yutaka <ebine.yutaka@sabel.jp>
 */
class Test_Controller_Redirector extends SabelTestCase
{
  public static function suite()
  {
    return self::createSuite("Test_Controller_Redirector");
  }
  
  public function setUp()
  {
    $config = new TestConfigMap();
    $config->route("default")
           ->uri(":controller/:action")
           ->module("index");
           
    $this->routing($config);
  }
  
  public function testIsRedirected()
  {
    $redirector = new Sabel_Controller_Redirector();
    $this->assertFalse($redirector->isRedirected());
  }
  
  public function testRedirect()
  {
    $redirector = new Sabel_Controller_Redirector();
    $redirector->to("a: test");
    $this->assertTrue($redirector->isRedirected());
    $this->assertEquals("index/test", $redirector->getUrl());
  }
  
  public function testRedirectWithParameters()
  {
    $redirector = new Sabel_Controller_Redirector();
    $redirector->to("a: test", array("page" => "1"));
    $this->assertTrue($redirector->isRedirected());
    $this->assertTrue($redirector->hasParameters());
    $this->assertEquals("index/test?page=1", $redirector->getUrl());
  }
  
  public function testUriParameter()
  {
    $redirector = new Sabel_Controller_Redirector();
    $redirector->to("n: default");
    $this->assertTrue($redirector->isRedirected());
    $this->assertEquals("index/index", $redirector->getUrl());
  }
  
  protected function routing($config)
  {
    $builder = new Sabel_Request_Builder();
    $request = new Sabel_Request_Object();
    
    foreach ($config->build() as $candidate) {
      if ($candidate->evaluate($builder->build($request, "index/index"))) {
        Sabel_Context::getContext()->setCandidate($candidate);
        break;
      }
    }
  }
}

class TestConfigMap extends Sabel_Map_Configurator
{
  public function configure() {}
}
