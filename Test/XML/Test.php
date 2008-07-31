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
  
  /**
   * @test
   */
  public function initialize()
  {
    $this->outputUsersXml();
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
    
    $this->assertEquals("tanaka", $users->user[0]->name[0]->getNodeValue());
    $this->assertEquals("18",     $users->user[0]->age[0]->getNodeValue());
    $this->assertEquals("suzuki", $users->user[1]->name[0]->getNodeValue());
    $this->assertEquals("25",     $users->user[1]->age[0]->getNodeValue());
    $this->assertEquals("satou",  $users->user[2]->name[0]->getNodeValue());
    $this->assertEquals("40",     $users->user[2]->age[0]->getNodeValue());
  }
  
  public function setSetNodeValue()
  {
    $xml = new Sabel_Xml_Document();
    $users = $this->loadXML($xml, "users");
    $users->user[0]->age[0]->setNodeValue("20");
    $this->saveXML($xml, "users");
    
    $xml = new Sabel_Xml_Document();
    $users = $this->loadXML($xml, "users");
    $this->assertEquals("20", $users->user[0]->age[0]->getNodeValue());
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
    
    $this->assertEquals("1", $users->user[0]->at("id"));
    $this->assertEquals("2", $users->user[1]->at("id"));
    $this->assertEquals("3", $users->user[2]->at("id"));
  }
  
  public function testInsertBefore()
  {
    $xml = new Sabel_Xml_Document();
    $users = $this->loadXML($xml, "users");
    
    $this->assertEquals("tanaka", $users->user[0]->name[0]->getNodeValue());
    $this->assertEquals("suzuki", $users->user[1]->name[0]->getNodeValue());
    $this->assertEquals("satou",  $users->user[2]->name[0]->getNodeValue());
    
    $aUser = $xml->createElement("user");
    $aUser->appendChild($xml->createElement("name", "yamada"));
    $aUser->appendChild($xml->createElement("age", "60"));
    
    $users->user[2]->insertBefore($aUser);
    $this->saveXML($xml, "users");
    
    //-------------------------------------
    
    $xml = new Sabel_Xml_Document();
    $users = $this->loadXML($xml, "users");
    $this->assertEquals("tanaka", $users->user[0]->name[0]->getNodeValue());
    $this->assertEquals("suzuki", $users->user[1]->name[0]->getNodeValue());
    $this->assertEquals("yamada", $users->user[2]->name[0]->getNodeValue());
    $this->assertEquals("satou",  $users->user[3]->name[0]->getNodeValue());
  }
  
  public function testInsertAfter()
  {
    $xml = new Sabel_Xml_Document();
    $users = $this->loadXML($xml, "users");
    
    $aUser = $xml->createElement("user");
    $aUser->appendChild($xml->createElement("name", "koike"));
    $aUser->appendChild($xml->createElement("age", "80"));
    
    $users->user[3]->insertAfter($aUser);
    $this->saveXML($xml, "users");
    
    //-------------------------------------
    
    $xml = new Sabel_Xml_Document();
    $users = $this->loadXML($xml, "users");
    $this->assertEquals("tanaka", $users->user[0]->name[0]->getNodeValue());
    $this->assertEquals("suzuki", $users->user[1]->name[0]->getNodeValue());
    $this->assertEquals("yamada", $users->user[2]->name[0]->getNodeValue());
    $this->assertEquals("satou",  $users->user[3]->name[0]->getNodeValue());
    $this->assertEquals("koike",  $users->user[4]->name[0]->getNodeValue());
  }
  
  public function testGetParent()
  {
    $xml = new Sabel_Xml_Document();
    $users = $this->loadXML($xml, "users");
    
    $age = $users->user[0]->age[0];
    $this->assertEquals("age", $age->tagName);
    $this->assertEquals("user", $age->getParent()->tagName);
    $this->assertEquals("users", $age->getParent("users")->tagName);
  }
  
  public function testGetFirstChild()
  {
    $xml = new Sabel_Xml_Document();
    $users = $this->loadXML($xml, "users");
    
    $aUser = $users->user[0];
    $this->assertEquals("name", $aUser->getFirstChild()->tagName);
  }
  
  public function testGetLastChild()
  {
    $xml = new Sabel_Xml_Document();
    $users = $this->loadXML($xml, "users");
    
    $aUser = $users->getLastChild();
    $this->assertEquals("user", $aUser->tagName);
    $this->assertEquals("age", $aUser->getLastChild()->tagName);
    $this->assertEquals("80", $aUser->getLastChild()->getNodeValue());
  }
  
  public function testGetNextSibling()
  {
    $xml = new Sabel_Xml_Document();
    $users = $this->loadXML($xml, "users");
    
    $aUser = $users->getFirstChild();
    $this->assertEquals("tanaka", $aUser->name[0]->getNodeValue());
    $aUser = $aUser->getNextSibling();
    $this->assertEquals("suzuki", $aUser->name[0]->getNodeValue());
    $aUser = $aUser->getNextSibling();
    $this->assertEquals("yamada", $aUser->name[0]->getNodeValue());
    $aUser = $aUser->getNextSibling();
    $this->assertEquals("satou", $aUser->name[0]->getNodeValue());
    $aUser = $aUser->getNextSibling();
    $this->assertEquals("koike", $aUser->name[0]->getNodeValue());
    $aUser = $aUser->getNextSibling();
    $this->assertEquals(null, $aUser);
  }
  
  public function testGetNextSiblings()
  {
    $xml = new Sabel_Xml_Document();
    $users = $this->loadXML($xml, "users");
    
    $elems = $users->user[2]->getNextSiblings();
    $this->assertEquals(2, $elems->length);
    $this->assertEquals("satou", $elems[0]->name[0]->getNodeValue());
    $this->assertEquals("koike", $elems[1]->name[0]->getNodeValue());
  }
  
  public function testGetPreviousSibling()
  {
    $xml = new Sabel_Xml_Document();
    $users = $this->loadXML($xml, "users");
    
    $aUser = $users->getLastChild();
    $this->assertEquals("koike", $aUser->name[0]->getNodeValue());
    $aUser = $aUser->getPreviousSibling();
    $this->assertEquals("satou", $aUser->name[0]->getNodeValue());
    $aUser = $aUser->getPreviousSibling();
    $this->assertEquals("yamada", $aUser->name[0]->getNodeValue());
    $aUser = $aUser->getPreviousSibling();
    $this->assertEquals("suzuki", $aUser->name[0]->getNodeValue());
    $aUser = $aUser->getPreviousSibling();
    
    $this->assertEquals("tanaka", $aUser->name[0]->getNodeValue());
    $aUser = $aUser->getPreviousSibling();
    $this->assertEquals(null, $aUser);
  }
  
  public function testGetPreviousSiblings()
  {
    $xml = new Sabel_Xml_Document();
    $users = $this->loadXML($xml, "users");
    
    $elems = $users->user[2]->getPreviousSiblings();
    $this->assertEquals(2, $elems->length);
    $this->assertEquals("suzuki", $elems[0]->name[0]->getNodeValue());
    $this->assertEquals("tanaka", $elems[1]->name[0]->getNodeValue());
    
    $elems->reverse();
    
    $this->assertEquals("tanaka", $elems[0]->name[0]->getNodeValue());
    $this->assertEquals("suzuki", $elems[1]->name[0]->getNodeValue());
  }
  
  public function testGetSiblings()
  {
    $xml = new Sabel_Xml_Document();
    $users = $this->loadXML($xml, "users");
    
    $elems = $users->user[2]->getSiblings();
    $this->assertEquals(4, $elems->length);
    $this->assertEquals("tanaka", $elems[0]->name[0]->getNodeValue());
    $this->assertEquals("suzuki", $elems[1]->name[0]->getNodeValue());
    $this->assertEquals("satou",  $elems[2]->name[0]->getNodeValue());
    $this->assertEquals("koike",  $elems[3]->name[0]->getNodeValue());
  }
  
  public function testFindFromAttribute()
  {
    $xml = new Sabel_Xml_Document();
    $users = $this->loadXML($xml, "find");
    
    $elems = $users->select("from user @id = 1");
    $this->assertEquals(1, $elems->length);
    
    $elem = $elems[0];
    $this->assertEquals("1", $elem->at("id"));
    $this->assertEquals("tanaka", $elem->profile[0]->name[0]->getNodeValue());
    
    $elems = $users->select("from user.foo.bar @type = 'b'");
    $this->assertEquals(1, $elems->length);
    
    $elem = $elems[0]->getParent("user");
    $this->assertEquals("2", $elem->at("id"));
    $this->assertEquals("suzuki", $elem->profile[0]->name[0]->getNodeValue());
  }
  
  public function testSelectByIsNull()
  {
    $xml = new Sabel_Xml_Document();
    $users = $this->loadXML($xml, "find");
    
    $elems = $users->select("from user.foo.bar @type IS NULL");
    $this->assertEquals(1, $elems->length);
    
    $elem = $elems[0]->getParent("user");
    $this->assertEquals("5", $elem->at("id"));
    $this->assertEquals("koike", $elem->profile[0]->name[0]->getNodeValue());
    
    $elems = $users->select("from user.foo.bar @type IS NOT NULL");
    $this->assertEquals(4, $elems->length);
    
    $elems = $users->select("from user test IS NULL");
    $this->assertEquals(3, $elems->length);
    
    $elems = $users->select("from user test IS NOT NULL");
    $this->assertEquals(2, $elems->length);
  }
  
  public function testReturnElement()
  {
    $xml = new Sabel_Xml_Document();
    $users = $this->loadXML($xml, "find");
    
    $elems = $users->select("from user.foo.bar @type = 'b'");
    $this->assertEquals("bar", $elems[0]->tagName);
    
    $elems = $users->select("from user.foo.bar @type IS NULL");
    $this->assertEquals("bar", $elems[0]->tagName);
    
    $elems = $users->select("from user.foo.bar @type IS NOT NULL");
    $this->assertEquals("bar", $elems[0]->tagName);
    
    //-------------------------------------------
    
    $elems = $users->select("from user foo.bar@type = 'b'");
    $this->assertEquals("user", $elems[0]->tagName);
    
    $elems = $users->select("from user foo.bar@type IS NULL");
    $this->assertEquals("user", $elems[0]->tagName);
    
    $elems = $users->select("from user foo.bar@type IS NOT NULL");
    $this->assertEquals("user", $elems[0]->tagName);
    
    //-------------------------------------------
    
    $aUser = $users->user[0];
    $elems = $aUser->select("from . @id = 2");
    $this->assertEquals("2", $elems[0]->at("id"));
    $this->assertEquals("suzuki", $elems[0]->profile[0]->name[0]->getNodeValue());
  }
  
  public function testLike()
  {
    $xml = new Sabel_Xml_Document();
    $users = $this->loadXML($xml, "find");
    
    $elems = $users->select("from user foo.bar.baz LIKE 'test%'");
    $this->assertEquals(2, $elems->length);
    $this->assertEquals("tanaka", $elems[0]->profile[0]->name[0]->getNodeValue());
    $this->assertEquals("suzuki", $elems[1]->profile[0]->name[0]->getNodeValue());
    
    $elems = $users->select("from user foo.bar.baz LIKE '%456%'");
    $this->assertEquals(3, $elems->length);
    $this->assertEquals("suzuki", $elems[0]->profile[0]->name[0]->getNodeValue());
    $this->assertEquals("satou",  $elems[1]->profile[0]->name[0]->getNodeValue());
    $this->assertEquals("koike",  $elems[2]->profile[0]->name[0]->getNodeValue());
  }
  
  public function testAnd()
  {
    $xml = new Sabel_Xml_Document();
    $users = $this->loadXML($xml, "find");
    
    $elems = $users->select("from user foo.bar.baz LIKE '%456%' AND test IS NOT NULL");
    $this->assertEquals(1, $elems->length);
    $this->assertEquals("koike", $elems[0]->profile[0]->name[0]->getNodeValue());
  }
  
  public function testOr()
  {
    $xml = new Sabel_Xml_Document();
    $users = $this->loadXML($xml, "find");
    
    $elems = $users->select("from user profile.age >= 60 OR profile.age <= 20");
    $this->assertEquals(3, $elems->length);
    $this->assertEquals("tanaka", $elems[0]->profile[0]->name[0]->getNodeValue());
    $this->assertEquals("1",      $elems[0]->at("id"));
    $this->assertEquals("yamada", $elems[1]->profile[0]->name[0]->getNodeValue());
    $this->assertEquals("3",      $elems[1]->at("id"));
    $this->assertEquals("koike",  $elems[2]->profile[0]->name[0]->getNodeValue());
    $this->assertEquals("5",      $elems[2]->at("id"));
  }
  
  protected function getXmlAsString($name)
  {
    return file_get_contents(XML_TEST_DIR . DS . "xml" . DS . $name . ".xml");
  }
  
  protected function loadXML(Sabel_Xml_Document $xml, $name)
  {
    return $xml->loadXML(file_get_contents(XML_TEST_DIR . DS . "xml" . DS . $name . ".xml"));
  }
  
  protected function saveXML(Sabel_Xml_Document $xml, $name = "_tmp")
  {
    return $xml->saveXML(XML_TEST_DIR . DS . "xml" . DS . $name . ".xml");
  }
  
  protected function outputUsersXml()
  {
    $xml = <<<XML
<?xml version="1.0" encoding="utf-8"?>
<users>
  <user>
    <name>tanaka</name>
    <age>18</age>
  </user>
  <user>
    <name>suzuki</name>
    <age>25</age>
  </user>
  <user>
    <name>satou</name>
    <age>40</age>
  </user>
</users>
XML;
    
    file_put_contents(XML_TEST_DIR . DS . "xml" . DS . "users.xml", $xml);
  }
}
