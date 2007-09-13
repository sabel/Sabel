<?php

require_once ('Test/Processor/Flow.php');

class Test_Processor_Tests extends SabelTestSuite
{
  public static function suite()
  {
    $suite = self::createSuite();
    
    $suite->addTest(Test_Processor_Flow::suite());
    
    return $suite;
  }
}
