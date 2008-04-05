<?php

/**
 * @category  Storage
 * @author    Ebine Yutaka <ebine.yutaka@sabel.jp>
 */
class Test_DB_Storage_Oci extends Test_DB_Storage_Test
{
  public static function suite()
  {
    return self::createSuite("Test_DB_Storage_Oci");
  }
  
  public function testInit()
  {
    $params = array("package"  => "sabel.db.oci",
                    "host"     => "127.0.0.1",
                    "user"     => "develop",
                    "password" => "develop",
                    "database" => "xe",
                    "charset"  => "UTF8");
    
    Sabel_DB_Config::add("default", $params);
    
    MODEL("SblStorage")->delete();
  }
}
