<?php

/**
 * testcase for sabel.db.Validator
 *
 * @category  DB
 * @author    Ebine Yutaka <ebine.yutaka@sabel.jp>
 */
class Test_DB_Validate extends SabelTestCase
{
  public static function suite()
  {
    return self::createSuite("Test_DB_Validate");
  }

  public function testRequired()
  {
    $ex = MODEL("Example");
    $validator = new Sabel_DB_Validator($ex);
    $errors = $validator->validate();
    
    $this->assertEquals(2, count($errors));
    $this->assertEquals("column1 is required.", $errors[0]);
    $this->assertEquals("column2 is required.", $errors[1]);
  }
  
  public function testMaxValue()
  {
    $ex = MODEL("Example");
    $ex->column1 = 3000000000;
    $ex->column2 = "a";
    
    $validator = new Sabel_DB_Validator($ex);
    $errors = $validator->validate();
    
    $this->assertEquals("column1 must be " . PHP_INT_MAX . " or less.", $errors[0]);
  }
  
  public function testMinValue()
  {
    $ex = MODEL("Example");
    $ex->column1 = 1;
    $ex->column2 = "a";
    $ex->column4 = 15;
    
    $validator = new Sabel_DB_Validator($ex);
    $errors = $validator->validate();
    
    $this->assertEquals("column4 must be 18 or more.", $errors[0]);
  }
  
  public function testMaxLength()
  {
    $ex = MODEL("Example");
    $ex->column1 = 1;
    $ex->column2 = "a";
    $ex->column3 = "123456789";
    
    $validator = new Sabel_DB_Validator($ex);
    $errors = $validator->validate();
    
    $this->assertEquals("column3 must be 8 characters or less.", $errors[0]);
  }
  
  public function testMinLength()
  {
    $ex = MODEL("Example");
    $ex->column1 = 1;
    $ex->column2 = "a";
    $ex->column3 = "123";
    
    $validator = new Sabel_DB_Validator($ex);
    $errors = $validator->validate();
    
    $this->assertEquals("column3 must be 4 characters or more.", $errors[0]);
  }
  
  public function testNotNumeric()
  {
    $ex = MODEL("Example");
    $ex->column1 = 1;
    $ex->column2 = "a";
    $ex->column4 = "hoge";
    
    $validator = new Sabel_DB_Validator($ex);
    $errors = $validator->validate();
    
    $this->assertEquals("column4 must be a numeric.", $errors[0]);
  }
  
  public function testMaxFloatValue()
  {
    $ex = MODEL("Example");
    $ex->column1 = 1;
    $ex->column2 = "a";
    $ex->column5 = 3.4028235E+39;
    
    $validator = new Sabel_DB_Validator($ex);
    $errors = $validator->validate();
    
    $this->assertEquals("column5 must be 3.4028235E+38 or less.", $errors[0]);
  }
  
  public function testMinFloatValue()
  {
    $ex = MODEL("Example");
    $ex->column1 = 1;
    $ex->column2 = "a";
    $ex->column5 = -3.4028235E+39;
    
    $validator = new Sabel_DB_Validator($ex);
    $errors = $validator->validate();
    
    $this->assertEquals("column5 must be -3.4028235E+38 or more.", $errors[0]);
  }
  
  public function testDateFormat()
  {
    $ex = MODEL("Example");
    $ex->column1 = 1;
    $ex->column2 = "a";
    $ex->column6 = "hogehoge";
    
    $validator = new Sabel_DB_Validator($ex);
    $errors = $validator->validate();
    
    $this->assertEquals("wrong column6 format.", $errors[0]);
    
    $ex->column6 = "2008-01-01";
    
    $validator = new Sabel_DB_Validator($ex);
    $errors = $validator->validate();
    $this->assertTrue(empty($errors));
  }
  
  public function testDatetimeFormat()
  {
    $ex = MODEL("Example");
    $ex->column1 = 1;
    $ex->column2 = "a";
    $ex->column7 = "hogehoge";
    
    $validator = new Sabel_DB_Validator($ex);
    $errors = $validator->validate();
    
    $this->assertEquals("wrong column7 format.", $errors[0]);
    
    $ex->column7 = "2008-01-01 10:10:10";
    
    $validator = new Sabel_DB_Validator($ex);
    $errors = $validator->validate();
    $this->assertTrue(empty($errors));
  }
  
  public function testBooleanValue()
  {
    $ex = MODEL("Example");
    $ex->column1 = 1;
    $ex->column2 = "a";
    $ex->column8 = "hogehoge";
    
    $validator = new Sabel_DB_Validator($ex);
    $errors = $validator->validate();
    
    $this->assertEquals("wrong column8 format.", $errors[0]);
    
    $ex->column8 = true;
    
    $validator = new Sabel_DB_Validator($ex);
    $errors = $validator->validate();
    $this->assertTrue(empty($errors));
    
    $ex->column8 = false;
    
    $validator = new Sabel_DB_Validator($ex);
    $errors = $validator->validate();
    $this->assertTrue(empty($errors));
  }
  
  public function testUnique()
  {
    if (extension_loaded("mysql")) {
      $params = array("package"  => "sabel.db.mysql",
                      "host"     => "127.0.0.1",
                      "user"     => "root",
                      "password" => "",
                      "database" => "sdb_test");
    } elseif (extension_loaded("pgsql")) {
      $params = array("package"  => "sabel.db.pgsql",
                      "host"     => "127.0.0.1",
                      "user"     => "root",
                      "password" => "",
                      "database" => "sdb_test");
    } elseif (extension_loaded("pdo_sqlite")) {
      $params = array("package"  => "sabel.db.pdo.sqlite",
                      "database" => "/usr/local/lib/php/Sabel/Test/data/sdb_test.sq3");
    } else {
      return false;
    }
    
    Sabel_DB_Config::add("default", $params);
    $driver = Sabel_DB_Driver::create("default");
    $driver->execute("DELETE FROM schema_test");
    $driver->execute("INSERT INTO schema_test(name, email) VALUES('hoge', 'validate.test@example.com')");
    
    $st = MODEL("SchemaTest");
    $st->name  = "fuga";
    $st->email = "validate.test@example.com";
    
    $validator = new Sabel_DB_Validator($st);
    $errors = $validator->validate();
    
    $this->assertEquals("'validate.test@example.com'(email) is unavailable.", $errors[0]);
  }
  
  public function testCustomEmailValidator()
  {
    Sabel_DB_Validate_Config::addValidator(
      array("function" => "emailAddressValidator",
            "model"    => "Example2",
            "column"   => "column2")
    );
    
    $ex2 = MODEL("Example2");
    $ex2->column1 = 1;
    $ex2->column2 = "hoge";
    
    $validator = new Sabel_DB_Validator($ex2);
    $errors = $validator->validate();
    
    $this->assertEquals("invalid email address.", $errors[0]);
    
    $ex2->column2 = "a@a.a";
    
    $validator = new Sabel_DB_Validator($ex2);
    $errors = $validator->validate();
    $this->assertTrue(empty($errors));
  }
  
  public function testMultipleCustomValidator()
  {
    Sabel_DB_Validate_Config::addValidator(
      array("function"  => "minLengthValidator",
            "model"     => "Example2",
            "column"    => "column2",
            "arguments" => 8)
    );
    
    $ex2 = MODEL("Example2");
    $ex2->column1 = 1;
    $ex2->column2 = "hoge";
    
    $validator = new Sabel_DB_Validator($ex2);
    $errors = $validator->validate();
    
    $this->assertEquals("invalid email address.", $errors[0]);
    
    $ex2->column2 = "a@a.a";
    
    $validator = new Sabel_DB_Validator($ex2);
    $errors = $validator->validate();
    
    $this->assertEquals("column2 must be 8 characters or more.", $errors[0]);
  }
  
  public function testCustomValidatorArguments()
  {
    Sabel_DB_Validate_Config::addValidator(
      array("function"  => "maxminValidator",
            "model"     => "Example2",
            "column"    => "column1",
            "arguments" => array(10, 100))
    );
    
    $ex2 = MODEL("Example2");
    $ex2->column1 = 1;
    $ex2->column2 = "hoge@example.com";
    
    $validator = new Sabel_DB_Validator($ex2);
    $errors = $validator->validate();
    $this->assertEquals("column1 must be 10 or more.", $errors[0]);
    
    $ex2->column1 = 120;
    
    $validator = new Sabel_DB_Validator($ex2);
    $errors = $validator->validate();
    $this->assertEquals("column1 must be 100 or less.", $errors[0]);
  }
  
  public function testCustomValidatorArguments2()
  {
    Sabel_DB_Validate_Config::addValidator(
      array("function"  => "maxminValidator",
            "model"     => "Example3",
            "column"    => "column1",
            "arguments" => array(20, 80))
    );
    
    $ex3 = MODEL("Example3");
    $ex3->column1 = 10;
    $ex3->column2 = "hoge@example.com";
    
    $validator = new Sabel_DB_Validator($ex3);
    $errors = $validator->validate();
    $this->assertEquals("column1 must be 20 or more.", $errors[0]);
    
    $ex3->column1 = 90;
    
    $validator = new Sabel_DB_Validator($ex3);
    $errors = $validator->validate();
    $this->assertEquals("column1 must be 80 or less.", $errors[0]);
  }
  
  public function testRetypeValidator()
  {
    Sabel_DB_Validate_Config::addValidator(
      array("function"  => "retypeValidator",
            "model"     => "Example3",
            "column"    => "column3",
            "arguments" => "retype")
    );
    
    $ex3 = MODEL("Example3");
    $ex3->column1 = 50;
    $ex3->column2 = "hoge@example.com";
    $ex3->column3 = "1p2a3s4s5w6o7r8d";
    $ex3->retype  = "abcde";
    
    $validator = new Sabel_DB_Validator($ex3);
    $errors = $validator->validate();
    $this->assertEquals("input values didn't match.", $errors[0]);
    
    $ex3->retype = "1p2a3s4s5w6o7r8d";
    
    $validator = new Sabel_DB_Validator($ex3);
    $errors = $validator->validate();
    $this->assertTrue(empty($errors));
  }
  
  public function testRegexColumn()
  {
    Sabel_DB_Validate_Config::addValidator(
      array("function" => "testValidator",
            "model"    => "Example4",
            "column"   => "col_*")
    );
    
    $ex4 = MODEL("Example4");
    $validator = new Sabel_DB_Validator($ex4);
    $errors = $validator->validate();
    $this->assertEquals(2, count($errors));
    $this->assertTrue(in_array("col_abc", $errors, true));
    $this->assertTrue(in_array("col_xyz", $errors, true));
    $this->assertFalse(in_array("abc_col", $errors, true));
    $this->assertFalse(in_array("xyz_col", $errors, true));
    
    Sabel_DB_Validate_Config::clearValidators();
  }
  
  public function testRegexColumn2()
  {
    Sabel_DB_Validate_Config::addValidator(
      array("function" => "testValidator",
            "model"    => "Example4",
            "column"   => "*_col")
    );
    
    $ex4 = MODEL("Example4");
    $validator = new Sabel_DB_Validator($ex4);
    $errors = $validator->validate();
    $this->assertEquals(2, count($errors));
    $this->assertFalse(in_array("col_abc", $errors, true));
    $this->assertFalse(in_array("col_xyz", $errors, true));
    $this->assertTrue(in_array("abc_col", $errors, true));
    $this->assertTrue(in_array("xyz_col", $errors, true));
    
    Sabel_DB_Validate_Config::clearValidators();
  }
}

function emailAddressValidator($model, $name, $localizedName)
{
  if ($model->$name !== null) {
    $regex = '/^[\w.\-_]+@([\w\-_]+\.)+[a-zA-Z]+$/';
    if (preg_match($regex, $model->$name) === 0) {
      return "invalid email address.";
    }
  }
}

function maxminValidator($model, $name, $localizedName, $min, $max)
{
  if ($model->$name !== null) {
    $value = $model->$name;
    if ($value < $min) {
      return "$name must be $min or more.";
    } elseif ($value > $max) {
      return "$name must be $max or less.";
    }
  }
}

function minLengthValidator($model, $name, $localizedName, $min)
{
  if ($model->$name !== null) {
    if (strlen($model->$name) < $min) {
      return "$name must be $min characters or more.";
    }
  }
}

function retypeValidator($model, $name, $localizedName, $reInput)
{
  if ($model->$name !== $model->$reInput) {
    return "input values didn't match.";
  } else {
    $model->unsetValue($reInput);
  }
}

function testValidator($model, $name, $localizedName)
{
  return $name;
}

class Schema_Example
{
  public static function get()
  {
    $cols = array();
    
    $cols['column1'] = array('type'      => Sabel_DB_Type::INT,
                             'min'       => -PHP_INT_MAX - 1,
                             'max'       => PHP_INT_MAX,
                             'increment' => false,
                             'nullable'  => false,
                             'primary'   => true,
                             'default'   => null);
                             
    $cols['column2'] = array('type'      => Sabel_DB_Type::STRING,
                             'max'       => 255,
                             'increment' => false,
                             'nullable'  => false,
                             'primary'   => false,
                             'default'   => null);
                             
    $cols['column3'] = array('type'      => Sabel_DB_Type::STRING,
                             'min'       => 4,
                             'max'       => 8,
                             'increment' => false,
                             'nullable'  => true,
                             'primary'   => false,
                             'default'   => null);
                             
    $cols['column4'] = array('type'      => Sabel_DB_Type::INT,
                             'min'       => 18,
                             'max'       => 120,
                             'increment' => false,
                             'nullable'  => true,
                             'primary'   => false,
                             'default'   => null);
                             
    $cols['column5'] = array('type'      => Sabel_DB_Type::FLOAT,
                             'min'       => -3.4028235E+38,
                             'max'       => 3.4028235E+38,
                             'increment' => false,
                             'nullable'  => true,
                             'primary'   => false,
                             'default'   => null);
                             
    $cols['column6'] = array('type'      => Sabel_DB_Type::DATE,
                             'increment' => false,
                             'nullable'  => true,
                             'primary'   => false,
                             'default'   => null);
                             
    $cols['column7'] = array('type'      => Sabel_DB_Type::DATETIME,
                             'increment' => false,
                             'nullable'  => true,
                             'primary'   => false,
                             'default'   => null);
                             
    $cols['column8'] = array('type'      => Sabel_DB_Type::BOOL,
                             'increment' => false,
                             'nullable'  => true,
                             'primary'   => false,
                             'default'   => false);
                             
    return $cols;
  }

  public function getProperty()
  {
    $property = array();

    $property["tableEngine"] = null;
    $property["uniques"]     = null;
    $property["fkeys"]       = null;

    return $property;
  }
}

class Schema_Example2
{
  public static function get()
  {
    $cols = array();

    $cols['column1'] = array('type'      => Sabel_DB_Type::INT,
                             'min'       => -PHP_INT_MAX - 1,
                             'max'       => PHP_INT_MAX,
                             'increment' => false,
                             'nullable'  => false,
                             'primary'   => true,
                             'default'   => null);

    $cols['column2'] = array('type'      => Sabel_DB_Type::STRING,
                             'max'       => 255,
                             'increment' => false,
                             'nullable'  => false,
                             'primary'   => false,
                             'default'   => null);

    return $cols;
  }

  public function getProperty()
  {
    $property = array();

    $property["tableEngine"] = null;
    $property["uniques"]     = null;
    $property["fkeys"]       = null;

    return $property;
  }
}

class Schema_Example3
{
  public static function get()
  {
    $cols = array();

    $cols['column1'] = array('type'      => Sabel_DB_Type::INT,
                             'min'       => -PHP_INT_MAX - 1,
                             'max'       => PHP_INT_MAX,
                             'increment' => false,
                             'nullable'  => false,
                             'primary'   => true,
                             'default'   => null);

    $cols['column2'] = array('type'      => Sabel_DB_Type::STRING,
                             'max'       => 255,
                             'increment' => false,
                             'nullable'  => false,
                             'primary'   => false,
                             'default'   => null);

    $cols['column3'] = array('type'      => Sabel_DB_Type::STRING,
                             'max'       => 255,
                             'increment' => false,
                             'nullable'  => true,
                             'primary'   => false,
                             'default'   => null);

    return $cols;
  }

  public function getProperty()
  {
    $property = array();

    $property["tableEngine"] = null;
    $property["uniques"]     = null;
    $property["fkeys"]       = null;

    return $property;
  }
}

class Schema_Example4
{
  public static function get()
  {
    $cols = array();
    
    $cols['col_abc'] = array('type'      => Sabel_DB_Type::STRING,
                             'max'       => 255,
                             'increment' => false,
                             'nullable'  => true,
                             'primary'   => false,
                             'default'   => null);
                             
    $cols['col_xyz'] = array('type'      => Sabel_DB_Type::STRING,
                             'max'       => 255,
                             'increment' => false,
                             'nullable'  => true,
                             'primary'   => false,
                             'default'   => null);
                             
    $cols['abc_col'] = array('type'      => Sabel_DB_Type::STRING,
                             'max'       => 255,
                             'increment' => false,
                             'nullable'  => true,
                             'primary'   => false,
                             'default'   => null);
                             
    $cols['xyz_col'] = array('type'      => Sabel_DB_Type::STRING,
                             'max'       => 255,
                             'increment' => false,
                             'nullable'  => true,
                             'primary'   => false,
                             'default'   => null);
                             
    return $cols;
  }

  public function getProperty()
  {
    $property = array();
    
    $property["tableEngine"] = null;
    $property["uniques"]     = null;
    $property["fkeys"]       = null;
    
    return $property;
  }
}
