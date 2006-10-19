<?php

/**
 * TestCase of Validate
 *
 * @category   TestCase
 * @package    org.sabel.TestCase
 * @author     Mori Reo <mori.reo@gmail.com>
 * @copyright  2002-2006 Mori Reo <mori.reo@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Test_Validate extends PHPUnit2_Framework_TestCase
{
  public static function suite()
  {
    return new PHPUnit2_Framework_TestSuite("Test_Validate");
  }
  
  public function testErrors()
  {
    $errors = new Sabel_Validate_Errors();
    $errors->add('name', 'error', null);
    $this->assertEquals('error', $errors->get('name')->getMessage());
  }
  
  public function testValidatorLower()
  {
    $data = array('shop_id' => 9.22337203685E+19);
    $aValidator = new TestModel_Validator();
    $errors = $aValidator->validate($data);
    $this->assertTrue($errors->hasError());
    $this->assertEquals(Sabel_Validate_Error::LOWER_THEN, $errors->get('shop_id')->getType());
  }
  
  public function testValidatorGrather()
  {
    $data = array('shop_id' => -9.22337203685E+19);
    $aValidator = new TestModel_Validator();
    $errors = $aValidator->validate($data);
    $this->assertTrue($errors->hasError());
    $this->assertEquals(Sabel_Validate_Error::GRATHER_THEN, $errors->get('shop_id')->getType());
  }
  
  public function testValidatorNotNull()
  {
    $data = array('id' => null);
    $aValidator = new TestModel_Validator();
    $errors = $aValidator->validate($data);
    $this->assertTrue($errors->hasError());
    $this->assertEquals(Sabel_Validate_Error::NOT_NULL, $errors->get('shop_id')->getType());
  }
  
  public function testValidatorStringLower()
  {
    $data = array('title' => 'abcdeabcdeabcdeabcdeabcdeabcdeabc');
    $aValidator = new TestModel_Validator();
    $errors = $aValidator->validate($data);
    $this->assertTrue($errors->hasError());
    $this->assertEquals(Sabel_Validate_Error::LOWER_THEN, $errors->get('title')->getType());
  }
  
  public function testValidatorHasNoError()
  {
    $data = array('id'      => '8.22337203685E+18',
                  'shop_id' => '9.22337203685E+18',
                  'title'   => 'abcdeabcdeabcde',
                  'body'    => 'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa
                                aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa
                                aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa
                                aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa
                                aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa
                                aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa
                                aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa
                                aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa
                                aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa
                                aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa
                                aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa
                                aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa
                                aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa
                                aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa');
                                
    $aValidator = new TestModel_Validator();
    $errors = $aValidator->validate($data);
    $this->assertFalse($errors->hasError());
    
  }
}

class Schema_Mock
{
  public function get()
  {
    $sql = array();
    $sql['id']       = array('type'      => 'INT',
                             'max'       =>  9.22337203685E+18,
                             'min'       => -9.22337203685E+18,
                             'increment' => true,
                             'nullable'  => false,
                             'primary'   => true,
                             'default'   => null);
    
    $sql['shop_id']  = array('type'      => 'INT',
                             'max'       =>  9.22337203685E+18,
                             'min'       => -9.22337203685E+18,
                             'increment' => false,
                             'nullable'  => false,
                             'primary'   => false,
                             'default'   => null);
    
    $sql['users_id'] = array('type'      => 'INT',
                             'max'       =>  9.22337203685E+18,
                             'min'       => -9.22337203685E+18,
                             'increment' => false,
                             'nullable'  => true,
                             'primary'   => false,
                             'default'   => null);
    
    $sql['title']    = array('type'      => 'STRING',
                             'max'       => 32,
                             'increment' => false,
                             'nullable'  => true,
                             'primary'   => false,
                             'default'   => null);
    
    $sql['body']     = array('type'      => 'TEXT',
                             'increment' => false,
                             'nullable'  => true,
                             'primary'   => false,
                             'default'   => null);
    
    $sql['date']     = array('type'      => 'TIMESTAMP',
                             'increment' => false,
                             'nullable'  => true,
                             'primary'   => false,
                             'default'   => 'CURRENT_TIMESTAMP');
    return $sql;
  }
}

class TestModel_Validator extends Sabel_Validate_Model
{
  public function __construct()
  {
    $this->schema = new Schema_Mock();
  }
}
