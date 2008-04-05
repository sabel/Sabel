<?php

/**
 * @category  Storage
 * @author    Ebine Yutaka <ebine.yutaka@sabel.jp>
 */
class Test_DB_Storage_SQLite extends Test_DB_Storage_Test
{
  public static function suite()
  {
    return self::createSuite("Test_DB_Storage_SQLite");
  }
  
  public function testInit()
  {
    $params = array("package"  => "sabel.db.pdo.sqlite",
                    "database" => SABEL_BASE . "/Test/data/sdb_test.sq3");
    
    Sabel_DB_Config::add("default", $params);
    
    MODEL("SblStorage")->delete();
  }
}
