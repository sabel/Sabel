<?php

if (!defined("GETTEXT_DEFAULT_DOMAIN_PATH")) {
  define("GETTEXT_DEFAULT_DOMAIN_PATH", dirname(__FILE__) . DIRECTORY_SEPARATOR . "locale");
}

/**
 * @category   I18n
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 */
class Test_I18n_PhpGettext extends SabelTestCase
{
  public static function suite()
  {
    return self::createSuite("Test_I18n_PhpGettext");
  }
  
  public function testI18n()
  {
    $gettext = Sabel_I18n_Gettext::getInstance();
    $gettext->init(Sabel_I18n_Gettext::PHP_GETTEXT, true);
    $gettext->setDomainPath(GETTEXT_DEFAULT_DOMAIN_PATH, "messages");
    $gettext->setDomain("messages");
    
    $env = Sabel_Environment::create();
    $env->set("HTTP_ACCEPT_LANGUAGE", "ja,en-us;q=0.7,en;q=0.3");
    
    $this->assertEquals("名前", __("name"));
    $this->assertEquals("住所", __("address"));
  }
  
  public function testDomainSet()
  {
    $this->assertEquals("名前", __("name"));
    $this->assertEquals("住所", __("address"));
    
    Sabel_I18n_Gettext::getInstance()->setDomain("hiragana");
    $this->assertEquals("なまえ", __("name"));
    $this->assertEquals("じゅうしょ", __("address"));
  }
  
  public function testDomainPathSet()
  {
    $gettext = Sabel_I18n_Gettext::getInstance();
    
    $path = dirname(__FILE__) . DIRECTORY_SEPARATOR . "locale2";
    $gettext->setDomainPath($path, "messages");
    $gettext->setDomain("messages");
    
    $this->assertEquals("名前2", __("name"));
    $this->assertEquals("住所2", __("address"));
  }
}
