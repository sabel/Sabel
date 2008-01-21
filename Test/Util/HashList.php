<?php

class Test_Util_HashList extends SabelTestCase
{
  public static function suite()
  {
    return self::createSuite("Test_Util_HashList");
  }
  
  // @todo make tests.
  public function testInsertPrevious()
  {
  }
}

class Process1
{
  public function process($list) { return "process1"; }
}

class Process2
{
  public function process($list)
  {
    $list->insertNext("b", "d", new Process4());
    return "process2";
  }
}

class Process3
{
  public function process($list) { return "process3"; }
}

class Process4
{
  public function process($list) { return "process4"; }
}

class Process5
{
  public function process($list) { return "process5"; }
}
