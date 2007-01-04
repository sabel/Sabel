<?php

if (PHPUNIT_VERSION === 2) {
  class SabelTestSuite extends PHPUnit2_Framework_TestCase
  {
    public static function main()
    {
      PHPUnit2_TextUI_TestRunner::run(self::suite());
    }
    
    protected static function createSuite()
    {
      return new PHPUnit2_Framework_TestSuite();
    }
  }
} elseif (PHPUNIT_VERSION === 3) {
  class SabelTestSuite extends PHPUnit2_Framework_TestCase
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
}