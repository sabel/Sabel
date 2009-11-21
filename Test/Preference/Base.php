<?php

/**
 * base test case of sabel Preference package
 *
 * @abstract
 * @category   Preference
 * @package    org.sabel.preference
 * @author     Mori Reo <mori.reo@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Test_Preference_Base extends SabelTestCase
{
  protected $pref = null;

  public function testGetInt()
  {
    $this->assertEquals(1, $this->pref->getInt("test", 1));

    $this->assertEquals(2, $this->pref->getInt("test", 2));

    $this->assertEquals(2, $this->pref->getInt("test"));
  }

  public function testGetIntWithString()
  {
    $this->assertEquals(1, $this->pref->getInt("test", "1"));

    $this->pref->setInt("test2", "2");
    $this->assertEquals(2, $this->pref->getInt("test2"));
  }

  public function testGetUndefinedKeyWithNotDefault()
  {
    try {
      $this->pref->getInt("undefined_key");
    } catch (Sabel_Exception_Runtime $e) {
      // exception occured this test pass ok
      return;
    }

    $this->fail();
  }
}
