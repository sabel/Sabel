<?php
// Call PersonTest::main() if this source file is executed directly.
if (!defined("PHPUnit2_MAIN_METHOD")) {
    define("PHPUnit2_MAIN_METHOD", "PersonTest::main");
}

require_once "PHPUnit2/Framework/TestCase.php";
require_once "PHPUnit2/Framework/TestSuite.php";

// You may remove the following line when all tests have been implemented.
require_once "PHPUnit2/Framework/IncompleteTestError.php";

require_once "RecordObject.php";
require_once "RecordClasses.php";
require_once "DBConnection.php";

/**
 * Test class for Person.
 * Generated by PHPUnit2_Util_Skeleton on 2006-06-23 at 15:02:19.
 */
class TestviewTest extends PHPUnit2_Framework_TestCase
{
  /**
   * Runs the test methods of this class.
   *
   * @access public
   * @static
   */
  public static function main() {
    require_once "PHPUnit2/TextUI/TestRunner.php";

    $suite  = new PHPUnit2_Framework_TestSuite("PersonTest");
    $result = PHPUnit2_TextUI_TestRunner::run($suite);
  }

  protected $test;

  /**
   * Sets up the fixture, for example, open a network connection.
   * This method is called before a test is executed.
   *
   * @access protected
   */
  protected function setUp()
  {
    //$pdo = new PDO('pgsql:host=192.168.0.222;dbname=2525e', 'pgsql', 'pgsql');

    $dbCon = array();
    $dbCon['dsn'] = 'pgsql:host=192.168.0.222;dbname=2525e';
    $dbCon['user'] = 'pgsql';
    $dbCon['pass'] = 'pgsql';

    DBConnection::addConnection('user', 'pdo', $dbCon);

    $obj = new Common_Record();

    $sql  = "CREATE TABLE test (id int2 NOT NULL,name varchar NOT NULL, blood varchar, test2_id int2,";
    $sql .= " CONSTRAINT test_pkey PRIMARY KEY (id) );";
    $obj->execute($sql);

    $this->test = new Test();

    $sql  = "CREATE TABLE test2 (id int2 NOT NULL,name varchar NOT NULL, test3_id int2,";
    $sql .= " CONSTRAINT test2_pkey PRIMARY KEY (id) );";
    $obj->execute($sql);

    $this->test2 = new Common_Record('test2');

    $sql  = "CREATE TABLE test3 (id int2 NOT NULL,name varchar NOT NULL,";
    $sql .= " CONSTRAINT test3_pkey PRIMARY KEY (id) );";
    $obj->execute($sql);

    $this->test3 = new Common_Record('test3');
  }

  /**
   * Tears down the fixture, for example, close a network connection.
   * This method is called after a test is executed.
   *
   * @access protected
   */
  protected function tearDown() {
    unset($this->test);
  }

  public function testMultipleInsert()
  {
    $insertData = array();
    $insertData[] = array('id' => 1, 'name' => 'tanaka',   'blood' => 'A',  'test2_id' => 1);
    $insertData[] = array('id' => 2, 'name' => 'yo_shida', 'blood' => 'B',  'test2_id' => 2);
    $insertData[] = array('id' => 3, 'name' => 'uchida',   'blood' => 'AB', 'test2_id' => 1);
    $insertData[] = array('id' => 4, 'name' => 'ueda',     'blood' => 'A',  'test2_id' => 3);
    $insertData[] = array('id' => 5, 'name' => 'seki',     'blood' => 'O',  'test2_id' => 2);
    $insertData[] = array('id' => 6, 'name' => 'uchida',   'blood' => 'A',  'test2_id' => 1);

    foreach ($insertData as $data) {
      $this->test->multipleInsert($data);
    }

    $ro = $this->test->select();
    $this->assertEquals(count($ro), 6);

    $obj = $this->test->selectOne(5);
    $this->assertEquals($obj->name, 'seki');

    $obj = $this->test->selectOne('name', 'seki');
    $this->assertEquals($obj->id, 5);
  }
    
  public function testInsert()
  {
    $this->test2->id = 1;
    $this->test2->name = 'test21';
    $this->test2->test3_id = '2';
    $this->test2->save();

    $this->test2->id = 2;
    $this->test2->name = 'test22';
    $this->test2->test3_id = '1';
    $this->test2->save();

    $this->test2->id = 3;
    $this->test2->name = 'test23';
    $this->test2->test3_id = '3';
    $this->test2->save();

    $ro = $this->test2->select();
    $this->assertEquals(count($ro), 3);

    $obj = $this->test2->selectOne(3);
    $this->assertEquals($obj->name, 'test23');

    $this->test3->id = 1;
    $this->test3->name = 'test31';
    $this->test3->save();

    $this->test3->id = 2;
    $this->test3->name = 'test32';
    $this->test3->save();

    $ro = $this->test3->select();
    $this->assertEquals(count($ro), 2);

    $this->test3->name('test31');
    $obj = $this->test3->selectOne();
    $this->assertEquals($obj->id, 1);
  }

  public function testUpdateOrInsert()
  {
    $test = new Test(7); // not found 
    $this->assertEquals($test->name, null);
    $this->assertEquals($test->blood, null);

    if ($test->is_selected()) {
      $test->blood = 'AB';
      $test->save();  // (update)
    } else {
      $test->name  = 'tanaka';
      $test->blood = 'B';
      $test->save();  // insert <= execute
    }

    //--------------------------------------------------------

    $test = new Test(7); // found 
    $this->assertEquals($test->name, 'tanaka');
    $this->assertEquals($test->blood, 'B');

    if ($test->is_selected()) {
      $test->blood = 'AB';
      $test->save();  // update <= execute
    } else {
      $test->name  = 'tanaka';
      $test->blood = 'B';
      $test->save();  // (insert)
    }

    $test = new Test(7);
    $this->assertEquals($test->name, 'tanaka');
    $this->assertEquals($test->blood, 'AB');
  }

  public function testDelete()
  {
    $obj = $this->test->selectOne(7);
    $this->assertEquals($obj->blood, 'AB');

    $this->test->delete(7);

    $obj = $this->test->selectOne(7);
    $this->assertNotEquals($obj->blood, 'AB');
    $this->assertEquals($obj->blood, null);
  }

  public function testCondition()
  {
    $this->test->setCondition('location_id', 2);
    $this->test->setCondition('LIKE_name', '%aki%');
    $con = $this->test->getCondition();

    $this->assertEquals($con['location_id'], 2);
    $this->assertEquals($con['LIKE_name'], '%aki%');

    $this->test->unsetCondition();

    //--------------------------------------------------

    $this->test->BET_location_id(23, 50);
    $con = $this->test->getCondition();

    foreach ($con as $key => $val) {
      $this->assertEquals($key, 'BET_location_id');
      $this->assertEquals($val[0], 23);
      $this->assertEquals($val[1], 50);
    }
  }

  public function testSelectDefaultResult()
  {
    $obj = $this->test->selectOne(1);

    $this->assertEquals($obj->id, 1);
    $this->assertEquals($obj->name, 'tanaka');
    $this->assertEquals($obj->blood, 'A');
    $this->assertEquals($obj->test2_id, 1);

    //----------------------------------------------

    $obj = new Test(1);

    $this->assertEquals($obj->id, 1);
    $this->assertEquals($obj->name, 'tanaka');
    $this->assertEquals($obj->blood, 'A');
    $this->assertEquals($obj->test2_id, 1);

    //----------------------------------------------

    $this->test->LIKE_name('%da%');
    $obj = $this->test->select();
    $this->assertEquals(count($obj), 4); // yo_shida, uchida, ueda, uchida

    $this->test->LIKE_name('%_%');
    $obj = $this->test->select();
    $this->assertEquals(count($obj), 1); // yo_shida

    $this->test->OR_id('3', '4');
    $obj = $this->test->select();
    $this->assertEquals($obj[0]->name, 'uchida');
    $this->assertEquals($obj[1]->name, 'ueda');
    $this->assertEquals($obj[2]->name, null);
  }

  public function testSelectChildResult()
  {
    $this->test->setSelectType(RecordObject::SELECT_CHILD);
    $this->test->setChildConstraint('limit', 10);
    $obj = $this->test->selectOne(1);

    $this->assertEquals($obj->id, 1);
    $this->assertEquals($obj->name, 'tanaka');
    $this->assertEquals($obj->blood, 'A');
    $this->assertEquals($obj->test2_id, 1);

    $child = $obj->test2[0];
    $this->assertEquals($child->id, 1);
    $this->assertEquals($child->name, 'test21');
    $this->assertEquals($child->test3_id, 2);

    $child2 = $child->test3[0];
    $this->assertEquals($child2->id, 2);
    $this->assertEquals($child2->name, 'test32');
  }

  public function testSelectViewResult()
  {
    $this->test->setSelectType(RecordObject::SELECT_VIEW);
    $obj = $this->test->selectOne(1);
      
    $this->assertEquals($obj->test_id, 1);
    $this->assertEquals($obj->test_name, 'tanaka');
    $this->assertEquals($obj->test_blood, 'A');
    $this->assertEquals($obj->test_test2_id, 1);
    $this->assertEquals($obj->test2_id, 1);
    $this->assertEquals($obj->test2_name, 'test21');
    $this->assertEquals($obj->test2_test3_id, 2);
    $this->assertEquals($obj->test3_id, 2);
    $this->assertEquals($obj->test3_name, 'test32');
  }

  public function testGetCount()
  {
    // all count ---------------------------------
    $count = $this->test->getCount();
    $this->assertEquals($count, 6);

    //--------------------------------------------
    $count = $this->test->getCount('< 5');
    $this->assertEquals($count, 4);

    $count = $this->test->getCount('id', '< 4');
    $this->assertEquals($count, 3);

    $this->test->id('< 3');
    $count = $this->test->getCount();
    $this->assertEquals($count, 2);
  }

  public function testGetNextNumber()
  {
    $next = $this->test->getNextNumber();
    $this->assertEquals($next, 7);

    $next = $this->test->getNextNumber('id');
    $this->assertEquals($next, 7);
  }
}

// Call PersonTest::main() if this source file is executed directly.
if (PHPUnit2_MAIN_METHOD == "TestviewTest::main") {
    TestviewTest::main();
}
?>
