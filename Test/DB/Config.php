<?php

/**
 * testcase for sabel.db.Config
 *
 * @category  DB
 * @author    Ebine Yutaka <ebine.yutaka@sabel.jp>
 */
class Test_DB_Config extends SabelTestCase
{
  public static function suite()
  {
    return self::createSuite("Test_DB_Config");
  }

  public function testConfigNotFound()
  {
    try {
      Sabel_DB_Config::initialize("/tmp/sdbconfig.php");
    } catch (Sabel_Exception_FileNotFound $notFound) {
      return;
    }
    
    $this->fail();
  }
  
  public function testInitialize()
  {
    Sabel_DB_Config::initialize(__FILE__);
    $config = Sabel_DB_Config::get("configtest");
    $this->assertEquals("localhost", $config["host"]);
    $this->assertEquals("mydb", $config["database"]);
  }
  
  public function testDefaultSchemaName()
  {
    $params = array("package"  => "sabel.db.mysql",
                    "database" => "mydb");
    
    Sabel_DB_Config::add("configtest", $params);
    $this->assertEquals("mydb", Sabel_DB_Config::getSchemaName("configtest"));
    
    $params = array("package"  => "sabel.db.pgsql",
                    "database" => "mydb");
    
    Sabel_DB_Config::add("configtest", $params);
    $this->assertEquals("public", Sabel_DB_Config::getSchemaName("configtest"));
    
    $params = array("package"  => "sabel.db.pdo.pgsql",
                    "database" => "mydb");
    
    Sabel_DB_Config::add("configtest", $params);
    $this->assertEquals("public", Sabel_DB_Config::getSchemaName("configtest"));
    
    $params = array("package"  => "sabel.db.oci",
                    "database" => "mydb", "user" => "webuser");
    
    Sabel_DB_Config::add("configtest", $params);
    $this->assertEquals("WEBUSER", Sabel_DB_Config::getSchemaName("configtest"));
    
    $params = array("package"  => "sabel.db.pdo.oci",
                    "database" => "mydb", "user" => "webuser");
    
    Sabel_DB_Config::add("configtest", $params);
    $this->assertEquals("WEBUSER", Sabel_DB_Config::getSchemaName("configtest"));
  }
  
  public function testSchemaNameSet()
  {
    $params = array("package"  => "sabel.db.mysql",
                    "database" => "mydb", "schema" => "hoge");
    
    Sabel_DB_Config::add("configtest", $params);
    $this->assertEquals("hoge", Sabel_DB_Config::getSchemaName("configtest"));
    
    $params = array("package"  => "sabel.db.pgsql",
                    "database" => "mydb", "schema" => "hoge");
    
    Sabel_DB_Config::add("configtest", $params);
    $this->assertEquals("hoge", Sabel_DB_Config::getSchemaName("configtest"));
    
    $params = array("package"  => "sabel.db.oci",
                    "database" => "mydb", "schema" => "HOGE");
    
    Sabel_DB_Config::add("configtest", $params);
    $this->assertEquals("HOGE", Sabel_DB_Config::getSchemaName("configtest"));
  }
  
  public function testSchemaNameOfCustomPackage()
  {
    $params = array("package"  => "my.db.org",
                    "database" => "mydb", "schema" => "hoge");
    
    Sabel_DB_Config::add("configtest", $params);
    $this->assertEquals("hoge", Sabel_DB_Config::getSchemaName("configtest"));
    
    $params = array("package"  => "my.db.org",
                    "database" => "mydb");
    
    Sabel_DB_Config::add("configtest", $params);
    
    try {
      Sabel_DB_Config::getSchemaName("configtest");
    } catch (Sabel_DB_Exception $e) {
      return;
    }
    
    $this->fail();
  }
}

function get_db_params($env = null)
{
  $params = array("configtest" => array(
                    "package"  => "sabel.db.mysql",
                    "host"     => "localhost",
                    "database" => "mydb",
                    "user"     => "root",
                    "password" => "")
                 );
  
  return $params;
}
