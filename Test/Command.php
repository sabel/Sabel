<?php

/**
 * test case for experimental code
 *
 * @category  Sakle
 * @author    Ebine Yutaka <ebine.yutaka@sabel.jp>
 */
class Test_Command extends SabelTestCase
{
  public static function suite()
  {
    return self::createSuite("Test_Command");
  }
  
  public function testMessage()
  {
    Sabel_Environment::create()->set("os", "WINNT");
    
    ob_start();
    Sabel_Command::success("success");
    $result = ob_get_clean();
    $this->assertEquals("[SUCCESS] success", rtrim($result));
    
    ob_start();
    Sabel_Command::warning("warning");
    $result = ob_get_clean();
    $this->assertEquals("[WARNING] warning", rtrim($result));
    
    ob_start();
    Sabel_Command::error("failure");
    $result = ob_get_clean();
    $this->assertEquals("[FAILURE] failure", rtrim($result));
    
    ob_start();
    Sabel_Command::message("message");
    $result = ob_get_clean();
    $this->assertEquals("[MESSAGE] message", rtrim($result));
  }
  
  public function testHasOption()
  {
    $args = array("rm", "-r");
    $this->assertTrue(Sabel_Command::hasOption("r", $args));
     
    $args = array("rm", "-r", "-f");
    $this->assertTrue(Sabel_Command::hasOption("f", $args));
     
    $args = array("rm", "-rf");
    $this->assertTrue(Sabel_Command::hasOption("f", $args));
  }
  
  public function testGetOption()
  {
    $args = array("cmd", "-d", "/var/tmp");
    $this->assertTrue(Sabel_Command::hasOption("d", $args));
    
    $this->assertEquals("/var/tmp", Sabel_Command::getOption("d", $args));
    $this->assertEquals(array("cmd"), $args);
  }
}
