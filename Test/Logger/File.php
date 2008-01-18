<?php

class Test_Logger_File extends SabelTestCase
{
  private $baseDir = "";
  
  public static function suite()
  {
    return self::createSuite("Test_Logger_File");
  }
  
  public function setUp()
  {
    $this->baseDir = RUN_BASE . DS . Sabel_Logger_File::DEFAULT_LOG_DIR . DS;
  }
  
  public function testOpen()
  {
    $logger = Sabel_Logger_File::singleton();
    $this->assertTrue(is_resource($logger->open("additional.log")));
    $this->assertFalse(is_resource(@$logger->open("nodir/additional.log")));
  }
  
  public function testIsOpend()
  {
    $logger = new Sabel_Logger_File("test.log");
    $this->assertTrue($logger->isOpend("test.log"));
    $logger->close();
  }
  
  public function testDefaultFileName()
  {
    $logger = new Sabel_Logger_File();
    $this->assertFalse($logger->isOpend("test.log"));
    $this->assertTrue($logger->isOpend("test.sabel.log"));
  }
  
  public function testContents()
  {
    $logger = new Sabel_Logger_File("hoge.log");
    $logger->write("hogehoge");
    $logger->close();
    
    $contents = file_get_contents($this->baseDir . "hoge.log");
    $this->assertTrue(strpos($contents, "hogehoge") !== false);
    $this->assertFalse(strpos($contents, "fugafuga") !== false);
    
    $logger = new Sabel_Logger_File("hoge.log");
    $logger->write("fugafuga");
    $logger->close();
    
    $contents = file_get_contents($this->baseDir . "hoge.log");
    $this->assertTrue(strpos($contents, "hogehoge") !== false);
    $this->assertTrue(strpos($contents, "fugafuga") !== false);
  }
  
  public function testEnd()
  {
    unlink($this->baseDir . "additional.log");
    unlink($this->baseDir . "test.log");
    unlink($this->baseDir . "hoge.log");
  }
}
