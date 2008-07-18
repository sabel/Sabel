<?php

/**
 * test case for sabel.xml.*
 *
 * @category  XML
 * @author    Ebine Yutaka <ebine.yutaka@sabel.jp>
 */
class Test_XML_Test extends SabelTestCase
{
  public static function suite()
  {
    return self::createSuite("Test_XML_Test");
  }
  
  public function testDocument()
  {
    $xml = new Sabel_Xml_Document();
    $test = $this->loadXML($xml, "simple");
    $this->assertEquals("test", $test->tagName);
    $this->assertEquals("utf-8", $xml->getEncoding());
    $this->assertEquals("1.0", $xml->getVersion());
  }
  
  public function testAttribute()
  {
    $xml = new Sabel_Xml_Document();
    $test = $this->loadXML($xml, "simple");
    $this->assertEquals("foo", $test->getChild("foo")->getAttribute("attr"));
    $this->assertEquals("bar", $test->getChild("bar")->getAttribute("attr"));
    $this->assertEquals("baz", $test->getChild("baz")->getAttribute("attr"));
  }
  
  public function testNodeValue()
  {
    $xml = new Sabel_Xml_Document();
    $test = $this->loadXML($xml, "simple");
    $this->assertEquals("footext", trim($test->getChild("foo")->getNodeValue()));
    $this->assertEquals("bartext", trim($test->getChild("bar")->getNodeValue()));
    $this->assertEquals("baztext", trim($test->getChild("baz")->getNodeValue()));
  }
  
  public function testElementsCount()
  {
    $xml = new Sabel_Xml_Document();
    $test = $this->loadXML($xml, "test");
    $this->assertEquals(2, $test->getElement()->getElementsByTagName("foo")->length);
    $this->assertEquals(1, $test->getChildren("foo")->length);
  }
  
  public function testCreateDocument()
  {
    $xml = new Sabel_Xml_Document();
    $xml->setEncoding("utf-8")->setVersion("1.0");
    
    $users = $xml->createElement("users");
    
    $aUser = $xml->createElement("user");
    $aUser->appendChild($xml->createElement("name", "tanaka"));
    $aUser->appendChild($xml->createElement("age", "18"));
    $users->appendChild($aUser);
    
    $aUser = $xml->createElement("user");
    $aUser->appendChild($xml->createElement("name", "suzuki"));
    $aUser->appendChild($xml->createElement("age", "25"));
    $users->appendChild($aUser);
    
    $aUser = $xml->createElement("user");
    $aUser->appendChild($xml->createElement("name", "satou"));
    $aUser->appendChild($xml->createElement("age", "40"));
    $users->appendChild($aUser);
    
    $xml->setDocumentElement($users);
    $this->saveXML($xml, "users");
  }
  
  public function testElements()
  {
    $xml = new Sabel_Xml_Document();
    $_users = $this->loadXML($xml, "users");
    $users = $_users->getChildren("user");
    $this->assertEquals(3, $users->length);
    
    foreach ($users as $i => $user) {}
    $this->assertEquals(2, $i);
    
    $this->assertEquals("tanaka", $users[0]->getChild("name")->getNodeValue());
    $this->assertEquals("18",     $users[0]->getChild("age")->getNodeValue());
    $this->assertEquals("suzuki", $users[1]->getChild("name")->getNodeValue());
    $this->assertEquals("25",     $users[1]->getChild("age")->getNodeValue());
    $this->assertEquals("satou",  $users[2]->getChild("name")->getNodeValue());
    $this->assertEquals("40",     $users[2]->getChild("age")->getNodeValue());
  }
  
  public function testSimpleAccess()
  {
    $xml = new Sabel_Xml_Document();
    $users = $this->loadXML($xml, "users");
    
    $this->assertEquals("tanaka", $users->user[0]->name->getNodeValue());
    $this->assertEquals("18",     $users->user[0]->age->getNodeValue());
    $this->assertEquals("suzuki", $users->user[1]->name->getNodeValue());
    $this->assertEquals("25",     $users->user[1]->age->getNodeValue());
    $this->assertEquals("satou",  $users->user[2]->name->getNodeValue());
    $this->assertEquals("40",     $users->user[2]->age->getNodeValue());
  }
  
  public function testSetAttribute()
  {
    $xml = new Sabel_Xml_Document();
    $users = $this->loadXML($xml, "users");
    $users->user[0]->setAttribute("id", 1);
    $users->user[1]->setAttribute("id", 2);
    $users->user[2]->setAttribute("id", 3);
    $this->saveXML($xml, "users");
    
    $xml = new Sabel_Xml_Document();
    $users = $this->loadXML($xml, "users");
    $this->assertEquals("1", $users->user[0]->getAttribute("id"));
    $this->assertEquals("2", $users->user[1]->getAttribute("id"));
    $this->assertEquals("3", $users->user[2]->getAttribute("id"));
  }
  
  public function testInsertBefore()
  {
    
  }
  
  public function testInsertAfter()
  {
    
  }
  
  protected function loadXML(Sabel_Xml_Document $xml, $name)
  {
    return $xml->loadXML(file_get_contents(XML_TEST_DIR . DS . "xml" . DS . $name . ".xml"));
  }
  
  protected function saveXML(Sabel_Xml_Document $xml, $name = "_tmp")
  {
    $string = $xml->saveXML();
    file_put_contents(XML_TEST_DIR . DS . "xml" . DS . $name . ".xml", $string);
  }
}
