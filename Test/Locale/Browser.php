<?php

class Test_Locale_Browser extends SabelTestCase
{
  public static function suite()
  {
    return self::createSuite("Test_Locale_Browser");
  }
  
  public function testAcceptLanguage()
  {
    $env = Sabel_Environment::create();
    $env->set("HTTP_ACCEPT_LANGUAGE", "en-us,en;q=0.7,ja;q=0.3");
    
    $browser = new Sabel_Locale_Browser();
    $this->assertEquals("en-us", $browser->getLocale());
    $languages = $browser->getLanguages();
    $this->assertEquals("en-us", $languages[0]);
    $this->assertEquals("en",    $languages[1]);
    $this->assertEquals("ja",    $languages[2]);
  }
  
  public function testAcceptLanguage2()
  {
    $env = Sabel_Environment::create();
    $env->set("HTTP_ACCEPT_LANGUAGE", "ja,en-us;q=0.7,en;q=0.3,fr;q=0.8");
    
    $browser = new Sabel_Locale_Browser();
    $this->assertEquals("ja", $browser->getLocale());
    $languages = $browser->getLanguages();
    $this->assertEquals("ja",    $languages[0]);
    $this->assertEquals("fr",    $languages[1]);
    $this->assertEquals("en-us", $languages[2]);
    $this->assertEquals("en",    $languages[3]);
  }
}
