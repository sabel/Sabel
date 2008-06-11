<?php

/**
 * testcase of sabel.Console
 *
 * @category  Sakle
 * @author    Ebine Yutaka <ebine.yutaka@sabel.jp>
 */
class Test_Console extends SabelTestCase
{
  public static function suite()
  {
    return self::createSuite("Test_Console");
  }
  
  public function testMessage()
  {
    $_SERVER["IS_WINDOWS"] = true;
    
    ob_start();
    Sabel_Console::success("success");
    $result = ob_get_clean();
    $this->assertEquals("[SUCCESS] success", rtrim($result));
    
    ob_start();
    Sabel_Console::warning("warning");
    $result = ob_get_clean();
    $this->assertEquals("[WARNING] warning", rtrim($result));
    
    ob_start();
    Sabel_Console::error("failure");
    $result = ob_get_clean();
    $this->assertEquals("[FAILURE] failure", rtrim($result));
    
    ob_start();
    Sabel_Console::message("message");
    $result = ob_get_clean();
    $this->assertEquals("[MESSAGE] message", rtrim($result));
  }
  
  public function testHasOption()
  {
    $args = array("rm", "-r");
    $this->assertTrue(Sabel_Console::hasOption("r", $args));
     
    $args = array("rm", "-r", "-f");
    $this->assertTrue(Sabel_Console::hasOption("r", $args));
    $this->assertTrue(Sabel_Console::hasOption("f", $args));
     
    $args = array("rm", "-rf");
    $this->assertTrue(Sabel_Console::hasOption("r", $args));
    $this->assertTrue(Sabel_Console::hasOption("f", $args));
  }
  
  public function testHasOption2()
  {
    $args = array("configure", "--foo");
    $this->assertTrue(Sabel_Console::hasOption("foo", $args));
  }
  
  public function testGetOption()
  {
    $args = array("cmd", "-d", "/var/tmp", "-f", "/tmp/test.txt");
    $this->assertTrue(Sabel_Console::hasOption("d", $args));
    $this->assertTrue(Sabel_Console::hasOption("f", $args));
    
    $opts = Sabel_Console::getOption("d", $args);
    $this->assertEquals("/var/tmp", $opts[0]);
    $this->assertEquals(array("cmd", "-f", "/tmp/test.txt"), $args);
    
    $opts = Sabel_Console::getOption("f", $args);
    $this->assertEquals("/tmp/test.txt", $opts[0]);
    $this->assertEquals(array("cmd"), $args);
  }
  
  public function testMultipleOptionValues()
  {
    $args = array("cmd", "-a", "hoge", "fuga", "foo", "-b", "test");
    $this->assertTrue(Sabel_Console::hasOption("a", $args));
    $this->assertTrue(Sabel_Console::hasOption("b", $args));
    $this->assertFalse(Sabel_Console::hasOption("c", $args));
    
    $opts = Sabel_Console::getOption("a", $args);
    $expected = array("hoge", "fuga", "foo");
    $this->assertEquals($expected, $opts);
    $expected = array("cmd", "-b", "test");
    $this->assertEquals($expected, $args);
    
    $opts = Sabel_Console::getOption("b", $args);
    $this->assertEquals("test", $opts[0]);
  }
}
