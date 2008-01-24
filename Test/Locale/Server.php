<?php

class Test_Locale_Server extends SabelTestCase
{
  private $locales = array();
  
  public static function suite()
  {
    return self::createSuite("Test_Locale_Server");
  }
  
  public function setUp()
  {
    $locales = array();
    foreach (explode(";", setlocale(LC_ALL, 0)) as $locale) {
      list ($key, $val) = explode("=", $locale);
      $locales[$key] = $val;
    }
    
    $this->locales = $locales;
  }
  
  public function testLocale()
  {
    $server = new Sabel_Locale_Server();
    $this->assertEquals($this->locales["LC_CTYPE"],    $server->ctype);
    $this->assertEquals($this->locales["LC_NUMERIC"],  $server->numeric);
    $this->assertEquals($this->locales["LC_MESSAGES"], $server->messages);
  }
}
