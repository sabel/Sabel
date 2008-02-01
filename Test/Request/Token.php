<?php

/**
 * test for sabel.request.Token
 *
 * @category Request
 * @author   Ebine Yutaka <ebine.yutaka@sabel.jp>
 */
class Test_Request_Token extends SabelTestCase
{
  public static function suite()
  {
    return self::createSuite("Test_Request_Token");
  }
  
  public function testCreateToken()
  {
    $token = new Sabel_Request_Token();
    $this->assertEquals(32, strlen($token->createValue()));
  }
  
  public function testCreateTokenWithPrefix()
  {
    $token = new Sabel_Request_Token();
    $this->assertEquals(39, strlen($token->createValue("prefix_")));
  }
  
  public function testToString()
  {
    $token = new Sabel_Request_Token();
    $token->setValue("1a2b3c4d5e6f7g8h9i0");
    $this->assertEquals("1a2b3c4d5e6f7g8h9i0", $token->__toString());
    $this->assertEquals("1a2b3c4d5e6f7g8h9i0", $token->toString());
    $this->assertEquals("1a2b3c4d5e6f7g8h9i0", $token->getValue());
  }
}
