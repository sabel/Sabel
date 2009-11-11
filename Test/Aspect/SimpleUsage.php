<?php

/**
 * TestCase of sabel.aspect.*
 *
 * @author Mori Reo <mori.reo@sabel.jp>
 */
class Test_Aspect_SimpleUsage extends SabelTestCase
{
  public static function suite()
  {
    return self::createSuite("Test_Aspect_SimpleUsage");
  }
  
  public function setUp()
  {
    Sabel_Container::addConfig("test_simpleusage", new Sabel_Test_Aspect_ConfigSimple());
  }
  
  public function testUsageConfig()
  {
    $updatable = load("Sabel_Test_Aspect_SimpleUsage_Person", "test_simpleusage");
    
    $this->assertTrue($updatable instanceof Sabel_Aspect_Proxy_Default);
    $updatable->updateState();
  }
}

class Sabel_Test_Aspect_ConfigSimple extends Sabel_Container_Injection
{
  public function configure()
  {
    $this->aspect("Sabel_Test_Aspect_Updatable")->advice("Sabel_Test_Aspect_UpdatableAdvice");
  }
}

interface Sabel_Test_Aspect_Updatable
{
  public function updateState();
}

class Sabel_Test_Aspect_SimpleUsage_Person implements Sabel_Test_Aspect_Updatable
{
  public function updateState()
  {
    // update state
  }
}

class Sabel_Test_Aspect_UpdatableAdvice
{
  /**
   * @before update.+
   */
  public function before($method, $arguments, $target)
  {
    // before
  }
  
  /**
   * @around update.+
   */
  public function around($invocation)
  {
    // around
  }
}