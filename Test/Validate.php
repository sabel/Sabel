<?php

class Test_Validate extends SabelTestCase
{
  public static function suite()
  {
    return self::createSuite("Test_Validate");
  }

  public function testValidate()
  {
    $model = new TargetModel1();
    $model->id = 10;
    $model->name = "hoge";
    $model->status = false;
    $model->registed = "2006-01-10 20:11:44";
    $model->point = 3000;

    $validator = new Sabel_DB_Validator($model);
    $result = $validator->validate();
    $this->assertFalse($validator->hasError());
  }

  public function testNullable()
  {
    $model = new TargetModel1();
    $model->name = "hoge";
    $model->status = false;
    $model->registed = "2006-01-10 20:11:44";
    $model->point = 3000;

    $validator = new Sabel_DB_Validator($model);
    $result = $validator->validate();
    $this->assertTrue($validator->hasError());

    foreach ($validator->getErrors() as $error) {
      $this->assertEquals($error, "Should not null. 'id'");
    }
  }

  public function testIntegerType()
  {
    $model = new TargetModel1();
    $model->id = 10;
    $model->name = "hoge";
    $model->status = false;
    $model->registed = "2006-01-10 20:11:44";
    $model->point = "hoge";

    $validator = new Sabel_DB_Validator($model);
    $validator->validate();

    foreach ($validator->getErrors() as $error) {
      $this->assertEquals($error, "Wrong Format. 'point'");
    }
  }

  public function testMaximum()
  {
    $model = new TargetModel1();
    $model->id = 10;
    $model->name = "hoge";
    $model->status = false;
    $model->registed = "2006-01-10 20:11:44";
    $model->point = 10000000000000;

    $validator = new Sabel_DB_Validator($model);
    $validator->validate();

    foreach ($validator->getErrors() as $error) {
      $this->assertEquals($error, "Too large. 'point'");
    }
  }

  public function testLength()
  {
    $model = new TargetModel1();
    $model->id = 10;
    $model->name = "hogehogehogehogehogehogehoge";
    $model->status = false;
    $model->registed = "2006-01-10 20:11:44";
    $model->point = 3000;

    $validator = new Sabel_DB_Validator($model);
    $validator->validate();

    foreach ($validator->getErrors() as $error) {
      $this->assertEquals($error, "Too long. 'name'");
    }
  }

  public function testDatetimeFormat()
  {
    $model = new TargetModel1();
    $model->id = 10;
    $model->name = "hoge";
    $model->registed = "2006-01-10T20:11:44";

    $validator = new Sabel_DB_Validator($model);
    $validator->validate();
    $this->assertFalse($validator->hasError());

    $model = new TargetModel1();
    $model->id = 10;
    $model->name = "hoge";
    $model->registed = "2006/01/10 00:11:44";

    $validator = new Sabel_DB_Validator($model);
    $validator->validate();
    $this->assertFalse($validator->hasError());

    $model = new TargetModel1();
    $model->id = 10;
    $model->name = "hoge";
    $model->registed = "2006-01-10T00:11:44+0900";

    $validator = new Sabel_DB_Validator($model);
    $validator->validate();
    $this->assertFalse($validator->hasError());

    $model = new TargetModel1();
    $model->id = 10;
    $model->name = "hoge";
    $model->registed = "1915-1-9 1:1:1 -0900";

    $validator = new Sabel_DB_Validator($model);
    $validator->validate();
    $this->assertFalse($validator->hasError());

    $model = new TargetModel1();
    $model->id = 10;
    $model->name = "hoge";
    $model->registed = "1915-a-9 1:1:1 -0900";

    $validator = new Sabel_DB_Validator($model);
    $validator->validate();
    $this->assertTrue($validator->hasError());

    $model = new TargetModel1();
    $model->id = 10;
    $model->name = "hoge";
    $model->registed = "1915-01-10 24:10:01 +0900";

    $validator = new Sabel_DB_Validator($model);
    $validator->validate();
    $this->assertTrue($validator->hasError());

    $model = new TargetModel1();
    $model->id = 10;
    $model->name = "hoge";
    $model->registed = "1915-13-10 23:10:01";

    $validator = new Sabel_DB_Validator($model);
    $validator->validate();
    $this->assertTrue($validator->hasError());

    $model = new TargetModel1();
    $model->id = 10;
    $model->name = "hoge";
    $model->registed = "1915-12-10 23:10:01";

    $validator = new Sabel_DB_Validator($model);
    $validator->validate();
    $this->assertFalse($validator->hasError());
  }

  public function testIgnore()
  {
    $model = new TargetModel1();
    $model->name = "hoge";
    $model->status = false;
    $model->registed = "2006-01-10 20:11:44";
    $model->point = 3000;

    $validator = new Sabel_DB_Validator($model);
    $result = $validator->validate(array("id"));
    $this->assertFalse($validator->hasError());

    $model = new TargetModel1();
    $model->status = false;
    $model->registed = "2006-01-10 20:11:44";
    $model->point = 3000;

    $validator = new Sabel_DB_Validator($model);
    $result = $validator->validate(array("id", "name"));
    $this->assertFalse($validator->hasError());
  }

  public function testPostProcess()
  {
    $this->assertFalse(PostProcess::$called);

    $processes = array("class"   => "PostProcess",
                       "methods" => array("call"));

    Sabel_DB_Validate_Config::registPostProcessing($processes);

    $model = new TargetModel1();
    $model->id = 10;
    $model->name = "hoge";
    $model->status = false;
    $model->registed = "2006-01-10 20:11:44";
    $model->point = 3000;

    $validator = new Sabel_DB_Validator($model);
    $result = $validator->validate();

    $this->assertTrue(PostProcess::$called);
  }

  public function testCustom1()
  {
    $custom = array("function" => "validate_function_custom1",
                    "model"    => "TargetModel1",
                    "column"   => "name");

    Sabel_DB_Validate_Config::registCustomValidation($custom);

    $model = new TargetModel1();
    $model->id = 10;
    $model->name = "hoge";
    $model->status = false;
    $model->registed = "2006-01-10 20:11:44";
    $model->point = 3000;

    $validator = new Sabel_DB_Validator($model);
    $validator->validate();
    $this->assertTrue($validator->hasError());

    foreach ($validator->getErrors() as $error) {
      $this->assertEquals($error, "value is dekai.");
    }

    Sabel_DB_Validate_Config::clearCustomValidations();
  }

  public function testCustom2()
  {
    $custom = array("function"  => "validate_function_custom1",
                    "model"     => "TargetModel1",
                    "column"    => "name",
                    "arguments" => array("5"));

    Sabel_DB_Validate_Config::registCustomValidation($custom);

    $model = new TargetModel1();
    $model->id = 10;
    $model->name = "hoge";
    $model->status = false;
    $model->registed = "2006-01-10 20:11:44";
    $model->point = 3000;

    $validator = new Sabel_DB_Validator($model);
    $validator->validate();
    $this->assertFalse($validator->hasError());

    Sabel_DB_Validate_Config::clearCustomValidations();
  }

  public function testLocalized()
  {
    $messages = array("length"   => "%sが長すぎます",
                      "maximum"  => "%sが大きすぎます",
                      "nullable" => "%sは省略できません",
                      "type"     => "%sの形式が不正です");

    Sabel_DB_Validate_Config::setMessages($messages);

    $localized = array("id"     => "ID",
                       "name"   => "名前",
                       "status" => "ステータス");

    Sabel_DB_Validate_Config::setLocalizedName("TargetModel1", $localized);

    $model = new TargetModel1();
    $model->id = 100000000000;
    $model->name = "hogehogehogehogehogehogehogehoge";
    $model->status = "hoge";
    $model->registed = "2006-01-10 20:11:44";
    $model->point = 3000;

    $validator = new Sabel_DB_Validator($model);
    $validator->validate();
    $this->assertTrue($validator->hasError());

    $errors = $validator->getErrors();
    $this->assertEquals(count($errors), 3);

    $error1 = $errors[0];
    $error2 = $errors[1];
    $error3 = $errors[2];

    $this->assertEquals($error1, "IDが大きすぎます");
    $this->assertEquals($error2, "ステータスの形式が不正です");
    $this->assertEquals($error3, "名前が長すぎます");

    Sabel_DB_Validate_Config::clearCustomValidations();
  }
}

class TargetModel1 extends Sabel_DB_Model
{

}

class Schema_TargetModel1
{
  public function get()
  {
    $cols = array();

    $cols['id']     = array('type'      => 'INT',
                            'max'       => 2147483647,
                            'min'       => -2147483648,
                            'increment' => true,
                            'nullable'  => false,
                            'primary'   => true,
                            'default'   => null);

    $cols['status'] = array('type'      => 'BOOL',
                            'increment' => false,
                            'nullable'  => true,
                            'primary'   => false,
                            'default'   => null);

    $cols['name']   = array('type'      => 'STRING',
                            'increment' => false,
                            'nullable'  => false,
                            'primary'   => false,
                            'max'       => 24,
                            'default'   => null);

    $cols['registed'] = array('type'      => 'DATETIME',
                              'increment' => false,
                              'nullable'  => true,
                              'primary'   => false,
                              'default'   => null);

    $cols['point'] = array('type'      => 'INT',
                           'max'       => 2147483647,
                           'min'       => -2147483648,
                           'increment' => false,
                           'nullable'  => true,
                           'primary'   => false,
                           'default'   => null);

    return $cols;
  }

  public function getProperty()
  {
    $property = array('primaryKey'   => 'id',
                      'incrementKey' => 'id',
                      'tableEngine'  => 'MyISAM');

    return $property;
  }
}

class PostProcess
{
  public static $called = false;

  public function call($errors, $model)
  {
    self::$called = true;
  }
}

function validate_function_custom1($value, $arg = 1)
{
  if (strlen($value) > $arg) {
    return "value is dekai.";
  }
}

function validate_function_custom2()
{

}
