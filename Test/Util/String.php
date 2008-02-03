<?php

class String extends Sabel_Util_String {}

/**
 * @category  Util
 * @author    Ebine Yutaka <ebine.yutaka@sabel.jp>
 */
class Test_Util_String extends SabelTestCase
{
  public static function suite()
  {
    return self::createSuite("Test_Util_String");
  }
  
  public function testIsEmpty()
  {
    $string = new String();
    $this->assertTrue($string->isEmpty());
  }
  
  public function testCharAt()
  {
    $string = new String("test");
    $this->assertEquals($string->charAt(0),  "t");
    $this->assertEquals($string->charAt(1),  "e");
    $this->assertEquals($string->charAt(3),  "t");
    $this->assertEquals($string->charAt(4),  null);
    $this->assertEquals($string->charAt(-1), null);
  }
  
  public function testIndexOf()
  {
    $string = new String("Hello World");
    $this->assertEquals($string->indexOf("W"), 6);
    $this->assertEquals($string->indexOf("o"), 4);
    $this->assertEquals($string->indexOf("o", 5), 7);
  }
  
  public function testTrim()
  {
    $string = new String("  Hello World  ");
    $this->assertTrue($string->trim()->equals("Hello World"));
    $this->assertEquals($string->length(), 11);
  }
  
  public function testRtrim()
  {
    $string = new String("  Hello World  ");
    $this->assertTrue($string->rtrim()->equals("  Hello World"));
    $this->assertEquals($string->length(), 13);
  }
  
  public function testLtrim()
  {
    $string = new String("  Hello World  ");
    $this->assertTrue($string->ltrim()->equals("Hello World  "));
    $this->assertEquals($string->length(), 13);
  }
  
  public function testToUpperCase()
  {
    $string = new String("Hello World");
    $this->assertTrue($string->toUpperCase()->equals("HELLO WORLD"));
  }
  
  public function testToLowerCase()
  {
    $string = new String("Hello World");
    $this->assertTrue($string->toLowerCase()->equals("hello world"));
  }
  
  public function testUcFirst()
  {
    $string = new String("test");
    $this->assertTrue($string->ucfirst()->equals("Test"));
  }
  
  public function testLcFirst()
  {
    $string = new String("ABCDE");
    $this->assertTrue($string->lcfirst()->equals("aBCDE"));
  }
  
  public function testSplit()
  {
    $string = new String("hoge:huga:foo:bar");
    $array  = $string->split(":");
    $values = $array->values();
    $this->assertTrue($values[0]->equals("hoge"));
    $this->assertTrue($values[1]->equals("huga"));
    $this->assertTrue($values[2]->equals("foo"));
    $this->assertTrue($values[3]->equals("bar"));
  }
  
  public function testReplace()
  {
    $string = new String("hoge huga");
    $this->assertTrue($string->replace("hoge", "foo")->equals("foo huga"));
  }
  
  public function testAppend()
  {
    $string = new String("hoge");
    $this->assertTrue($string->append("hoge")->equals("hogehoge"));
    
    $hoge = new String("hoge");
    $huga = new String("huga");
    $this->assertTrue($hoge->append($huga)->equals("hogehuga"));
  }
  
  public function testEquals()
  {
    $string = new String("hoge");
    $this->assertTrue($string->equals("hoge"));
    $this->assertTrue($string->equals("huga", "hoge"));
    $this->assertFalse($string->equals("huga"));
    $this->assertFalse($string->equals("huga", "foo"));
    
    $hoge1 = new String("hoge");
    $hoge2 = new String("hoge");
    
    $this->assertTrue($hoge1->equals($hoge2));
  }
  
  public function testSha1()
  {
    $string = new String("hoge");
    $this->assertEquals($string->sha1()->toString(), sha1("hoge"));
  }
  
  public function testMd5()
  {
    $string = new String("hoge");
    $this->assertEquals($string->md5()->toString(), md5("hoge"));
  }
  
  public function testSucc()
  {
    $string = new String("a");
    $this->assertTrue($string->succ()->equals("b"));
    $this->assertTrue($string->succ()->equals("c"));
    
    $string = new String("00");
    $this->assertTrue($string->succ()->equals("01"));
    $this->assertTrue($string->succ()->equals("02"));
    
    $string = new String("99");
    $this->assertTrue($string->succ()->equals("100"));
    $this->assertTrue($string->succ()->equals("101"));
    
    $string = new String("y");
    $this->assertTrue($string->succ()->equals("z"));
    $this->assertTrue($string->succ()->equals("aa"));
    $this->assertTrue($string->succ()->equals("ab"));
    
    $string = new String("Y");
    $this->assertTrue($string->succ()->equals("Z"));
    $this->assertTrue($string->succ()->equals("AA"));
    $this->assertTrue($string->succ()->equals("AB"));
    
    $string = new String("ay");
    $this->assertTrue($string->succ()->equals("az"));
    $this->assertTrue($string->succ()->equals("ba"));
    $this->assertTrue($string->succ()->equals("bb"));
    
    $string = new String("aY");
    $this->assertTrue($string->succ()->equals("aZ"));
    $this->assertTrue($string->succ()->equals("bA"));
    $this->assertTrue($string->succ()->equals("bB"));
    
    $string = new String("0Y");
    $this->assertTrue($string->succ()->equals("0Z"));
    $this->assertTrue($string->succ()->equals("1A"));
    $this->assertTrue($string->succ()->equals("1B"));
    
    $string = new String("9Y");
    $this->assertTrue($string->succ()->equals("9Z"));
    $this->assertTrue($string->succ()->equals("10A"));
    $this->assertTrue($string->succ()->equals("10B"));
    
    $string = new String("A998");
    $this->assertTrue($string->succ()->equals("A999"));
    $this->assertTrue($string->succ()->equals("B000"));
    $this->assertTrue($string->succ()->equals("B001"));
  }
  
  public function testSubString()
  {
    $string = new String("Hello World");
    
    $str = $string->substring(6);
    $this->assertTrue($str->equals("World"));
    $this->assertTrue($string->equals("Hello World"));
    
    $str = $string->substring(6, 3);
    $this->assertTrue($str->equals("Wor"));
    
    $str = $string->substring(1, -1);
    $this->assertTrue($str->equals("ello Worl"));
  }
  
  public function testInsert()
  {
    $string = new String("Hello World");
    $string->insert(6, "PHP ");
    $this->assertTrue($string->equals("Hello PHP World"));

    $string = new String("Hello World");
    $string->insert(0, "PHP. ");
    $this->assertTrue($string->equals("PHP. Hello World"));
  }
}
