<?php

class Test_Util extends SabelTestCase
{
  public static function suite()
  {
    return self::createSuite("Test_Util");
  }
  
  public function testInsertPreviousAndNext()
  {
    $list = new Sabel_Util_List("test", new StdClass());
    
    for ($i=0; $i < 299; $i++) {
      $list->insertNext("test{$i}", new StdClass());
    }
    $this->assertEquals(300, $list->size());
    
    for ($i=0; $i<300; $i++) {
      $list->insertPrevious("test{$i}", new StdClass());
    }
    $this->assertEquals(600, $list->size());
  }
  
  public function testFindByName()
  {
    $list = new Sabel_Util_list("test", new StdClass());
    
    $target = new StdClass();
    $target->value = "ebine";
    $next = $list->insertNext("target", $target);
    $next->insertNext("test2", new StdClass());
    
    $obj = $list->find("target");
    
    $this->assertTrue(($obj instanceof Sabel_Util_List));
    $this->assertTrue(is_object($obj));
    $this->assertEquals("ebine", $obj->current->value);
    $this->assertEquals("test", $obj->getFirst()->name);
    $this->assertEquals("ebine", $obj->getFirst()->next->current->value);
    $this->assertEquals("test2", $obj->getFirst()->next->next->name);
  }
}
