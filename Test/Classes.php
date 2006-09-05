<?php

require_once('PHPUnit2/Framework/TestCase.php');

class Test_Classes extends PHPUnit2_Framework_TestCase
{
  public static function suite()
  {
    return new PHPUnit2_Framework_TestSuite("Test_Classes");
  }
  
  public function testStringPosition()
  {
    $s = String::create('');
    $this->assertTrue($s->isEmpty());
    $this->assertTrue(String::create("test")->isNotEmpty());
    $this->assertTrue(String::create("test")->isntEmpty());
    $this->assertFalse(String::create("test")->isEmpty());
  }
  
  public function testStringTrim()
  {
    $this->assertEquals("test", String::create("test ")->rtrim());
    $this->assertEquals("test", String::create(" test")->ltrim());
    $this->assertEquals("test", String::create(" test ")->trim());
    
    $string = String::create(" test test ");
    $string->trim();
    $this->assertEquals("test test", $string->value);
  }
  
  public function testStringReplace()
  {
    $this->assertEquals("TEST", String::create("TEZT")->replace("Z", "S"));
  }
  
  public function testStringChangeCase()
  {
    $this->assertEquals("TEST", String::create("test")->toUpper());
    $this->assertEquals("test", String::create("TEST")->toLower());
    $this->assertEquals("Test", String::create("test")->toUpperFirst());
  }
  
  public function testStringExplode()
  {
    $this->assertEquals(1, count(String::create("test")->explode('/')));
    $this->assertEquals(2, count(String::create("test/test")->explode('/')));
    $this->assertEquals(2, count(String::create("test/test-test")->explode('/')));
  }
  
  public function testStringHash()
  {
    $this->assertEquals(sha1("test"), String::create("test")->sha1());
    $this->assertEquals(md5("test"), String::create("test")->md5());
  }
  
  public function testStringSucc()
  {
    $str = String::create("A");
    $this->assertEquals("B", $str->succ());
    $this->assertEquals("C", $str->succ());
    
    unset($str);
    $str = String::create("ABC001");
    $this->assertEquals("ABC002", $str->succ());
    $this->assertEquals("ABC003", $str->succ());
    
    unset($str);
    $str = String::create("A999");
    $this->assertEquals("B000", $str->succ());
    
    unset($str);
    $str = String::create("Z999");
    $this->assertEquals("AA000", $str->succ());
    $this->assertEquals("AA001", $str->succ());
    
    unset($str);
    $str = String::create("z9Z9");
    $this->assertEquals("aa0A0", $str->succ());
    
    unset($str);
    $str = String::create("-99");
    $this->assertEquals("-100", $str->succ());
    
    unset($str);
    $str = String::create("aZ9$");
    $this->assertEquals("aZ9$", $str->succ());
    
    // test for advance a place.
    unset($str);
    $str = String::create("0");
    for ($i = 1; $i < 100; ++$i) {
      $this->assertEquals((string) $i, $str->succ());
    }
  }
  
  public function testIterator()
  {
    $string = new String("test for iterator");
    $counter = 0;
    foreach ($string as $str) {
      $this->assertTrue(is_string($str->export()));;
      $this->assertTrue($str->isNotEmpty());
      ++$counter;
    }
    $this->assertEquals($counter, $string->length());
  }
  
  public function estPerformance()
  {
    $var = "test";
    $notObjectStart = microtime();
    for ($i = 0; $i < 10000; $i++) {
      (!empty($var));
    }
    $notObjectEnd = microtime();
    $notObject = ($notObjectEnd - $notObjectStart);
    echo "\nnot object use taken \t $notObject sec\n";
    
    $objectStart = microtime();
    $str = new String($var);
    for ($i = 0; $i < 10000; $i++) {
      $str->isNotEmpty();
    }
    $objectEnd = microtime();
    $object = ($objectEnd - $objectStart);
    echo "object use taken \t $object sec \n";
    
    echo " : " . ($notObject - $object) . "\n";
    echo " : " . ($object - $notObject) . "\n\n";
  }
  
}
