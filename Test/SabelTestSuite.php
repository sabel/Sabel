<?php

class SabelTestSuite extends PHPUnit_Framework_TestCase
{
  public static function main()
  {
    PHPUnit_TextUI_TestRunner::run(self::suite());
  }
  
  protected static function createSuite()
  {
    return new PHPUnit_Framework_TestSuite();
  }
}
