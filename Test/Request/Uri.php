<?php

/**
 * test for sabel.request.Uri
 *
 * @category Request
 * @author   Ebine Yutaka <ebine.yutaka@sabel.jp>
 */
class Test_Request_Uri extends SabelTestCase
{
  public static function suite()
  {
    return self::createSuite("Test_Request_Uri");
  }
  
  public function testEmptyUri()
  {
    $uri = new Sabel_Request_Uri("");
    $this->assertEquals("", $uri->__toString());
  }
  
  public function testSimpleUri()
  {
    $uri = new Sabel_Request_Uri("index/index");
    $this->assertEquals("index/index", $uri->__toString());
  }
  
  public function testSimpleUri2()
  {
    $uri = new Sabel_Request_Uri("index/test/hoge/fuga/foo/bar/baz");
    $this->assertEquals("index/test/hoge/fuga/foo/bar/baz", $uri->__toString());
  }
  
  public function testSize()
  {
    $uri = new Sabel_Request_Uri("index/test/hoge/fuga/foo/bar/baz");
    $this->assertEquals(7, $uri->size());
  }
  
  public function testGet()
  {
    $uri = new Sabel_Request_Uri("index/test/hoge/fuga/foo/bar/baz");
    
    $this->assertEquals("test", $uri->get(1));
    $this->assertEquals("foo",  $uri->get(4));
    $this->assertEquals(null,   $uri->get(100));
  }
  
  public function testSet()
  {
    $uri = new Sabel_Request_Uri("index/test/hoge/fuga/foo/bar/baz");
    $uri->set(1, "index");
    
    $this->assertEquals("index", $uri->get(1));
    $this->assertEquals("foo",   $uri->get(4));
    $this->assertEquals(null,    $uri->get(100));
  }
  
  public function testToArray()
  {
    $uri = new Sabel_Request_Uri("index/test");
    $this->assertEquals(array("index", "test"), $uri->toArray());
    
    $uri = new Sabel_Request_Uri("index/test/hoge");
    $this->assertEquals(array("index", "test", "hoge"), $uri->toArray());
  }
  
  public function testType()
  {
    $uri = new Sabel_Request_Uri("index/test.html");
    $this->assertEquals("html", $uri->type());
    
    $uri = new Sabel_Request_Uri("index/test.tar.gz");
    $this->assertEquals("tar.gz", $uri->type());
  }
}
