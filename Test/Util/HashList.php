<?php

class Test_Util_HashList extends SabelTestCase
{
  public static function suite()
  {
    return self::createSuite("Test_Util_HashList");
  }
  
  public function testAdd()
  {
    $list = new Sabel_Util_HashList();
    $list->add("a", "1");
    $list->add("b", "2");
    $list->add("c", "3");
    
    $this->assertEquals(3, $list->count());
    $this->assertEquals(3, count($list));
    
    $threw = false;
    
    try {
      $list->add("a", "hoge");
    } catch (Exception $e) {
      $threw = true;
    }
    
    $this->assertTrue($threw);
  }
  
  public function testRemove()
  {
    $list = new Sabel_Util_HashList();
    $list->add("a", "1");
    $list->add("b", "2");
    $list->add("c", "3");
    
    $this->assertEquals(3, $list->count());
    
    $list->remove("b");
    $this->assertEquals(2, $list->count());
    $this->assertEquals("1", $list->get("a"));
    $this->assertNull($list->get("b"));
    $this->assertEquals("3", $list->get("c"));
    
    $this->assertEquals("1", $list->next());
    $this->assertEquals("3", $list->next());
  }
  
  public function testInsertPrevious()
  {
    $list = new Sabel_Util_HashList();
    $list->add("a", "1");
    $list->add("b", "2");
    $list->add("c", "3");
    
    $list->insertPrevious("c", "d", "4");
    $this->assertEquals(4, $list->count());
    
    $this->assertEquals("1", $list->next());
    $this->assertEquals("2", $list->next());
    $this->assertEquals("4", $list->next());
    $this->assertEquals("3", $list->next());
  }
  
  public function testInsertNext()
  {
    $list = new Sabel_Util_HashList();
    $list->add("a", "1");
    $list->add("b", "2");
    $list->add("c", "3");
    
    $list->insertNext("a", "d", "4");
    $this->assertEquals(4, $list->count());
    
    $this->assertEquals("1", $list->next());
    $this->assertEquals("4", $list->next());
    $this->assertEquals("2", $list->next());
    $this->assertEquals("3", $list->next());
  }
  
  public function testFirst()
  {
    $list = new Sabel_Util_HashList();
    $list->add("a", "1");
    $list->add("b", "2");
    $list->add("c", "3");
    
    $this->assertEquals("1", $list->next());
    $this->assertEquals("2", $list->next());
    $this->assertEquals("3", $list->next());
    $this->assertNull($list->next());
    
    $list->first();
    
    $this->assertEquals("1", $list->next());
    $this->assertEquals("2", $list->next());
    $this->assertEquals("3", $list->next());
    $this->assertNull($list->next());
  }
  
  public function testLast()
  {
    $list = new Sabel_Util_HashList();
    $list->add("a", "1");
    $list->add("b", "2");
    $list->add("c", "3");
    
    $list->last();
    
    $this->assertEquals("3", $list->previous());
  }
  
  public function testPrevious()
  {
    $list = new Sabel_Util_HashList();
    $list->add("a", "1");
    $list->add("b", "2");
    $list->add("c", "3");
    
    $list->last();
    
    $this->assertEquals("3", $list->previous());
    $this->assertEquals("2", $list->previous());
    $this->assertEquals("1", $list->previous());
  }
  
  public function testCursor()
  {
    $list = new Sabel_Util_HashList();
    $list->add("a", "1");
    $list->add("b", "2");
    $list->add("c", "3");
    
    $this->assertEquals("1", $list->next());
    $this->assertEquals("2", $list->next());
    $this->assertEquals("3", $list->next());
    $this->assertEquals("2", $list->previous());
    $this->assertEquals("1", $list->previous());
    $this->assertEquals("2", $list->next());
    $this->assertEquals("1", $list->previous());
    $this->assertEquals("2", $list->next());
    $this->assertEquals("3", $list->next());
  }
  
  public function testDynamicInsert1()
  {
    $list = new Sabel_Util_HashList();
    $list->add("a", "1");
    $list->add("b", "2");
    $list->add("c", "3");
    
    $this->assertEquals("1", $list->next());
    $this->assertEquals("2", $list->next());
    $list->insertNext("b", "d", "4");
    $this->assertEquals("4", $list->next());
    $this->assertEquals("3", $list->next());
    $this->assertNull($list->next());
    
    $list = new Sabel_Util_HashList();
    $list->add("a", "1");
    $list->add("b", "2");
    $list->add("c", "3");
    
    $this->assertEquals("1", $list->next());
    $list->insertNext("c", "d", "4");
    
    $this->assertEquals("2", $list->next());
    $this->assertEquals("3", $list->next());
    $this->assertEquals("4", $list->next());
    $this->assertNull($list->next());
  }
  
  public function testDynamicInsert2()
  {
    $list = new Sabel_Util_HashList();
    $list->add("a", "1");
    $list->add("b", "2");
    $list->add("c", "3");
    
    $this->assertEquals("1", $list->next());
    $this->assertEquals("2", $list->next());
    $list->insertPrevious("b", "d", "4");
    $this->assertEquals("3", $list->next());
    $this->assertNull($list->next());
    
    $list = new Sabel_Util_HashList();
    $list->add("a", "1");
    $list->add("b", "2");
    $list->add("c", "3");
    
    $this->assertEquals("1", $list->next());
    $list->insertPrevious("c", "d", "4");
    
    $this->assertEquals("2", $list->next());
    $this->assertEquals("4", $list->next());
    $this->assertEquals("3", $list->next());
    $this->assertNull($list->next());
  }
  
  public function testDynamicInsert3()
  {
    $list = new Sabel_Util_HashList();
    $list->add("a", "1");
    $list->add("b", "2");
    $list->add("c", "3");
    
    $list->last();
    $this->assertEquals("3", $list->previous());
    $list->insertPrevious("c", "d", "4");
    $this->assertEquals("4", $list->previous());
    $this->assertEquals("2", $list->previous());
    $this->assertEquals("1", $list->previous());
    
    $list = new Sabel_Util_HashList();
    $list->add("a", "1");
    $list->add("b", "2");
    $list->add("c", "3");
    
    $list->last();
    $this->assertEquals("3", $list->previous());
    $list->insertPrevious("a", "d", "4");
    $this->assertEquals("2", $list->previous());
    $this->assertEquals("1", $list->previous());
    $this->assertEquals("4", $list->previous());
  }
  
  public function testDynamicInsertAndCursor()
  {
    $list = new Sabel_Util_HashList();
    $list->add("a", "1");
    $list->add("b", "2");
    $list->add("c", "3");
    
    $this->assertEquals("1", $list->next());
    $list->insertNext("b", "d", "4"); // 1 2 4 3
    $this->assertEquals("2", $list->next());
    $this->assertEquals("4", $list->next());
    $list->insertPrevious("b", "e", "5"); // 1 5 2 4 3
    $this->assertEquals("3", $list->next());
    $this->assertEquals("4", $list->previous());
    $list->insertPrevious("c", "f", "6"); // 1 5 2 4 6 3
    $this->assertEquals("6", $list->next());
    $this->assertEquals("3", $list->next());
    $this->assertNull($list->next());
    
    $list->last();
    $list->insertNext("c", "g", "7"); // 1 5 2 4 6 3 7
    
    $this->assertEquals("7", $list->previous());
    $this->assertEquals("3", $list->previous());
    $this->assertEquals("6", $list->previous());
    $this->assertEquals("4", $list->previous());
    $this->assertEquals("2", $list->previous());
    $this->assertEquals("5", $list->previous());
    $this->assertEquals("1", $list->previous());
    $this->assertNull($list->previous());
  }
  
  public function testDynamicRemoveAndCursor()
  {
    $list = new Sabel_Util_HashList();
    $list->add("a", "1");
    $list->add("b", "2");
    $list->add("c", "3");
    $list->add("d", "4");
    $list->add("e", "5");
    $list->add("f", "6");
    
    $this->assertEquals("1", $list->next());
    $this->assertEquals("2", $list->next());
    $list->remove("d"); // 1 2 3 5 6
    $this->assertEquals("3", $list->next());
    $this->assertEquals("5", $list->next());
    $list->remove("e"); // 1 2 3 6
    $this->assertEquals("6", $list->next());
    $this->assertEquals("3", $list->previous());
    $list->remove("f"); // 1 2 3
    $this->assertEquals("2", $list->previous());
    $list->insertPrevious("a", "g", "0"); // 0 1 2 3
    $list->remove("a"); // 0 2 3
    $this->assertEquals("0", $list->previous());
    $this->assertNull($list->previous());
    
    $list->last();
    $list->add("h", "8"); // 0 2 3 8
    
    $this->assertEquals("8", $list->previous());
    $this->assertEquals("3", $list->previous());
    $this->assertEquals("2", $list->previous());
    $this->assertEquals("0", $list->previous());
    $this->assertNull($list->previous());
  }
}
