<?php

// Call PersonTest::main() if this source file is executed directly.
if (!defined("PHPUnit2_MAIN_METHOD")) {
    define("PHPUnit2_MAIN_METHOD", "Test_Edo::main");
}

require_once "PHPUnit2/Framework/TestCase.php";
require_once "PHPUnit2/Framework/TestSuite.php";

// You may remove the following line when all tests have been implemented.
require_once "PHPUnit2/Framework/IncompleteTestError.php";

require_once('sabel/Functions.php');
require_once('sabel/core/Context.php');

require_once "sabel/edo/RecordObject.php";
require_once "sabel/edo/InformationSchema.php";
require_once "sabel/edo/DBConnection.php";

require_once "sabel/edo/Query.php";

require_once "sabel/edo/driver/Interface.php";
require_once "sabel/edo/driver/Pdo.php";
require_once "sabel/edo/driver/Pgsql.php";

class Test_Edo extends PHPUnit2_Framework_TestCase
{
  public static function main() {
    require_once "PHPUnit2/TextUI/TestRunner.php";

    $suite  = new PHPUnit2_Framework_TestSuite("PersonTest");
    $result = PHPUnit2_TextUI_TestRunner::run($suite);
  }

  public function __construct()
  {
    $helper = new PgsqlHelper();
    //$helper = new MysqlHelper();

    $helper->dropTables();
    $helper->createTables();
  }

  protected function setUp()
  {
    $this->test      = new Test();
    $this->test2     = new Sabel_Edo_CommonRecord('test2');
    $this->test3     = new Sabel_Edo_CommonRecord('test3');
    $this->customer  = new Customer();
    $this->order     = new Sabel_Edo_CommonRecord('customer_order');
    $this->orderLine = new Sabel_Edo_CommonRecord('order_line');
    $this->telephone = new Sabel_Edo_CommonRecord('customer_telephone');
  }

  protected function tearDown()
  {
  }

  public function testConstraint()
  {
    $insertData   = array();
    $insertData[] = array('id' => 1, 'name' => 'tanaka');
    $insertData[] = array('id' => 2, 'name' => 'ueda');
    
    foreach ($insertData as $data) {
      $this->customer->multipleInsert($data);
    }

    $this->assertEquals($this->customer->getCount(), 2);
    
    $insertData   = array();
    $insertData[] = array('id' => 1, 'customer_id' => 1);
    $insertData[] = array('id' => 2, 'customer_id' => 1);
    $insertData[] = array('id' => 3, 'customer_id' => 2);
    $insertData[] = array('id' => 4, 'customer_id' => 2);
    $insertData[] = array('id' => 5, 'customer_id' => 1);
    $insertData[] = array('id' => 6, 'customer_id' => 1);

    foreach ($insertData as $data) {
      $this->order->multipleInsert($data);
    }

    $o = new Sabel_Edo_CommonRecord('customer_order');
    $o->setSelectType(Sabel_Edo_RecordObject::WITH_PARENT_OBJECT);
    $res = $o->select();
    $this->assertEquals((int)$res[0]->customer->id, 1);
    $this->assertEquals((int)$res[2]->customer->id, 2);

    $this->assertEquals($this->order->getCount(), 6);
    
    $cu  = new Customer();
    $cus = $cu->select();
    $this->assertEquals((int)$cus[0]->customer_order[0]->id, 1);
    $this->assertEquals((int)$cus[0]->customer_order[1]->id, 2);
    $this->assertEquals((int)$cus[1]->customer_order[0]->id, 3);
    $this->assertEquals((int)$cus[1]->customer_order[1]->id, 4);
    $this->assertEquals((int)$cus[0]->customer_order[2]->id, 5);
    $this->assertEquals((int)$cus[0]->customer_order[3]->id, 6);
    
    $cu = new Customer();
    $cu->setChildConstraint('customer_order', array('order' => 'id desc'));
    $cus = $cu->select();
    $this->assertEquals((int)$cus[0]->customer_order[0]->id, 6);
    $this->assertEquals((int)$cus[0]->customer_order[1]->id, 5);
    $this->assertEquals((int)$cus[1]->customer_order[0]->id, 4);
    $this->assertEquals((int)$cus[1]->customer_order[1]->id, 3);
    $this->assertEquals((int)$cus[0]->customer_order[2]->id, 2);
    $this->assertEquals((int)$cus[0]->customer_order[3]->id, 1);
    
    $cu  = new Customer();
    $cu->setChildConstraint('customer_order', array('offset' => 1));
    $cus = $cu->select();
    $this->assertEquals((int)$cus[0]->customer_order[0]->id, 2);
    $this->assertEquals((int)$cus[1]->customer_order[0]->id, 4);
    $this->assertEquals((int)$cus[0]->customer_order[1]->id, 5);
    $this->assertEquals((int)$cus[0]->customer_order[2]->id, 6);
    
    $cu  = new Customer();
    $cu->setChildConstraint('customer_order', array('limit' => 2));
    $cus = $cu->select();
    $this->assertEquals((int)$cus[0]->customer_order[0]->id, 1);
    $this->assertEquals((int)$cus[0]->customer_order[1]->id, 2);
    $this->assertEquals((int)$cus[1]->customer_order[0]->id, 3);
    $this->assertEquals((int)$cus[1]->customer_order[1]->id, 4);
    $this->assertEquals($cus[0]->customer_order[2]->id, null);
    $this->assertEquals($cus[0]->customer_order[3]->id, null);
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
    $this->assertEquals((int)$obj->id, 5);

    $insertData = array();
    $insertData[] = array('id' => 1,  'customer_order_id' => 5, 'amount' => 1000,  'item_id' => 2);
    $insertData[] = array('id' => 2,  'customer_order_id' => 1, 'amount' => 3000,  'item_id' => 1);
    $insertData[] = array('id' => 3,  'customer_order_id' => 2, 'amount' => 5000,  'item_id' => 3);
    $insertData[] = array('id' => 4,  'customer_order_id' => 2, 'amount' => 8000,  'item_id' => 1);
    $insertData[] = array('id' => 5,  'customer_order_id' => 4, 'amount' => 9000,  'item_id' => 3);
    $insertData[] = array('id' => 6,  'customer_order_id' => 3, 'amount' => 1500,  'item_id' => 2);
    $insertData[] = array('id' => 7,  'customer_order_id' => 5, 'amount' => 2500,  'item_id' => 3);
    $insertData[] = array('id' => 8,  'customer_order_id' => 1, 'amount' => 3000,  'item_id' => 1);
    $insertData[] = array('id' => 9,  'customer_order_id' => 6, 'amount' => 10000, 'item_id' => 1);
    $insertData[] = array('id' => 10, 'customer_order_id' => 6, 'amount' => 50000, 'item_id' => 2);
    $insertData[] = array('id' => 11, 'customer_order_id' => 1, 'amount' => 500,   'item_id' => 3);
    
    foreach ($insertData as $data) {
      $this->orderLine->multipleInsert($data);
    }
    $this->assertEquals($this->orderLine->getCount(), 11);
    
    $insertData   = array();
    $insertData[] = array('id' => 1,  'customer_id' => 1, 'telephone' => '09011111111');
    $insertData[] = array('id' => 2,  'customer_id' => 2, 'telephone' => '09022221111');
    $insertData[] = array('id' => 3,  'customer_id' => 1, 'telephone' => '09011112222');
    $insertData[] = array('id' => 4,  'customer_id' => 2, 'telephone' => '09022222222');
    
    foreach ($insertData as $data) {
      $this->telephone->multipleInsert($data);
    }

    $this->assertEquals($this->orderLine->getCount(), 11);
    
    $tree = new Sabel_Edo_CommonRecord('tree');
    $insertData   = array();
    $insertData[] = array('id' => 1,  'name' => 'A');
    $insertData[] = array('id' => 2,  'name' => 'B');
    $insertData[] = array('id' => 3,  'tree_id' => 1, 'name' => 'A3');
    $insertData[] = array('id' => 4,  'name' => 'C');
    $insertData[] = array('id' => 5,  'tree_id' => 1, 'name' => 'A5');
    $insertData[] = array('id' => 6,  'tree_id' => 2, 'name' => 'B6');
    $insertData[] = array('id' => 7,  'tree_id' => 3, 'name' => 'A3-7');
    $insertData[] = array('id' => 8,  'tree_id' => 4, 'name' => 'C8');
    $insertData[] = array('id' => 9,  'tree_id' => 2, 'name' => 'B9');
    $insertData[] = array('id' => 10, 'tree_id' => 6, 'name' => 'B6-10');
    $insertData[] = array('id' => 11, 'tree_id' => 4, 'name' => 'C11');

    foreach ($insertData as $data) {
      $tree->multipleInsert($data);
    }

    $student = new Sabel_Edo_CommonRecord('student');
    $insertData   = array();
    $insertData[] = array('name' => 'tom',   'birth' => '1983/08/17');
    $insertData[] = array('name' => 'john',  'birth' => '1983/08/18');
    $insertData[] = array('name' => 'bob',   'birth' => '1983/08/19');
    $insertData[] = array('name' => 'marcy', 'birth' => '1983/08/20');
    $insertData[] = array('name' => 'ameri', 'birth' => '1983/08/21');

    foreach ($insertData as $data) {
      $student->multipleInsert($data);
    }

    $course = new Sabel_Edo_CommonRecord('course');
    $insertData   = array();
    $insertData[] = array('name' => 'Mathematics');
    $insertData[] = array('name' => 'Physics');
    $insertData[] = array('name' => 'Science');
    $insertData[] = array('name' => 'Economic');
    $insertData[] = array('name' => 'Psychology');

    foreach ($insertData as $data) {
      $course->multipleInsert($data);
    }

    $sc = new Sabel_Edo_CommonRecord('student_course');
    $insertData   = array();
    $insertData[] = array('student_id' => 1, 'course_id' => 1);
    $insertData[] = array('student_id' => 1, 'course_id' => 2);
    $insertData[] = array('student_id' => 1, 'course_id' => 3);

    $insertData[] = array('student_id' => 2, 'course_id' => 2);
    $insertData[] = array('student_id' => 2, 'course_id' => 3);
    $insertData[] = array('student_id' => 2, 'course_id' => 4);
    $insertData[] = array('student_id' => 2, 'course_id' => 5);

    $insertData[] = array('student_id' => 3, 'course_id' => 1);
    $insertData[] = array('student_id' => 3, 'course_id' => 2);
    $insertData[] = array('student_id' => 3, 'course_id' => 4);
    $insertData[] = array('student_id' => 3, 'course_id' => 5);

    $insertData[] = array('student_id' => 4, 'course_id' => 3);
    $insertData[] = array('student_id' => 4, 'course_id' => 4);

    $insertData[] = array('student_id' => 5, 'course_id' => 1);
    $insertData[] = array('student_id' => 5, 'course_id' => 2);
    $insertData[] = array('student_id' => 5, 'course_id' => 3);
    $insertData[] = array('student_id' => 5, 'course_id' => 4);
    $insertData[] = array('student_id' => 5, 'course_id' => 5);

    foreach ($insertData as $data) {
      $sc->multipleInsert($data);
    }

    $users = new Users();
    $insertData   = array();
    $insertData[] = array('name' => 'Tarou'  , 'age' => 20, 'location' => 'TOKYO');
    $insertData[] = array('name' => 'Hanako' , 'age' => 22, 'location' => 'CHIBA');

    foreach ($insertData as $data) {
      $users->multipleInsert($data);
    }
  }

  public function testInsert()
  {
    $test2 = new Sabel_Edo_CommonRecord('test2');
    $test2->id = 1;
    $test2->name = 'test21';
    $test2->test3_id = '2';
    $test2->save();

    $test2 = new Sabel_Edo_CommonRecord('test2');
    $test2->id = 2;
    $test2->name = 'test22';
    $test2->test3_id = '1';
    $test2->save();
    
    $test2 = new Sabel_Edo_CommonRecord('test2');
    $test2->id = 3;
    $test2->name = 'test23';
    $test2->test3_id = '3';
    $test2->save();
    
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
    $this->assertEquals((int)$obj->id, 1);
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
  
  public function testRemove()
  {
    $obj = $this->test->selectOne(7);
    $this->assertEquals($obj->blood, 'AB');
    
    $this->test->remove(7);
    
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
  
  public function testProjection()
  {
    $obj = $this->test->selectOne(2);
    $this->assertEquals((int)$obj->id, 2);
    $this->assertEquals($obj->name, 'yo_shida');
    $this->assertEquals($obj->blood, 'B');
    $this->assertEquals((int)$obj->test2_id, 2);
    
    //--------------------------------------------------
    
    $test = new Test();
    $test->setProjection(array(id,blood));
    
    $obj2 = $test->selectOne(2);
    $this->assertEquals((int)$obj2->id, 2);
    $this->assertNotEquals($obj2->name, 'yo_shida');
    $this->assertEquals($obj2->blood, 'B');
    $this->assertNotEquals((int)$obj2->test2_id, 2);
  }

  public function testMultipleSelect()
  {
    $obj = new Test();
    $user1 = $obj->selectOne(1);
    $user2 = $obj->selectOne(2);
    
    $this->assertEquals((int)$user1->id, 1);
    $this->assertEquals($user1->name, 'tanaka');
    
    $this->assertEquals((int)$user2->id, 2);
    $this->assertEquals($user2->name, 'yo_shida');
  }
  
  public function testSelectDefaultResult()
  {
    $obj = $this->test->selectOne(1);
    
    $this->assertEquals((int)$obj->id, 1);
    $this->assertEquals($obj->name, 'tanaka');
    $this->assertEquals($obj->blood, 'A');
    $this->assertEquals((int)$obj->test2_id, 1);
    
    //----------------------------------------------
    
    $obj = new Test(1);
    
    $this->assertEquals((int)$obj->id, 1);
    $this->assertEquals($obj->name, 'tanaka');
    $this->assertEquals($obj->blood, 'A');
    $this->assertEquals((int)$obj->test2_id, 1);
    
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
    
    $this->test->OR_id('<2', '>5');
    $obj = $this->test->select();
    $this->assertEquals((int) $obj[0]->id, 1);
    $this->assertEquals((int) $obj[1]->id, 6);
  }

  public function testInfiniteLoop()
  {
    $in1 = new Sabel_Edo_CommonRecord('infinite1');
    $in1->id           = 1;
    $in1->infinite2_id = 2;
    $in1->save();
    
    $in2 = new Sabel_Edo_CommonRecord('infinite2');
    $in2->id           = 2;
    $in2->infinite1_id = 1;
    $in2->save();
    
    $in1->setSelectType(Sabel_Edo_RecordObject::WITH_PARENT_OBJECT);
    $objs = $in1->select();
    $obj = $objs[0];
    
    $this->assertEquals($obj->infinite2_id, $obj->infinite2->id);
    $this->assertEquals((int)$obj->infinite2->infinite1_id, 1);
    $this->assertEquals($obj->infinite2->infinite1, null);
  }
  
  public function testSelectParentObject()
  {
    $obj = $this->test->selectOne(1);
    
    $this->assertEquals((int)$obj->id, 1);
    $this->assertEquals($obj->name, 'tanaka');
    $this->assertEquals($obj->blood, 'A');
    $this->assertEquals((int)$obj->test2_id, 1);
    
    $parent = $obj->test2;
    $this->assertEquals((int)$parent->id, 1);
    $this->assertEquals($parent->name, 'test21');
    $this->assertEquals((int)$parent->test3_id, 2);
    
    $parent2 = $parent->test3;
    $this->assertEquals((int)$parent2->id, 2);
    $this->assertEquals($parent2->name, 'test32');
  }
  
  public function testSelectParentView()
  {
    $this->test->setSelectType(Sabel_Edo_RecordObject::WITH_PARENT_VIEW);
    $obj = $this->test->selectOne(1);
    
    $this->assertEquals((int)$obj->id, 1);
    $this->assertEquals($obj->name, 'tanaka');
    $this->assertEquals($obj->blood, 'A');
    $this->assertEquals((int)$obj->test2_id, 1);
    $this->assertEquals($obj->test2_name, 'test21');
    $this->assertEquals((int)$obj->test2_test3_id, 2);
    $this->assertEquals((int)$obj->test3_id, 2);
    $this->assertEquals($obj->test3_name, 'test32');
  }
  
  public function testGetChild()
  {
    $this->customer->setChildConstraint(array('limit' => 10));
    $cu = $this->customer->selectOne(1);
    $this->assertEquals($cu->name, 'tanaka');
    
    $orders = $cu->customer_order;
    $this->assertEquals(count($orders), 4);
    
    $this->assertEquals((int)$orders[0]->id, 1);
    $this->assertEquals((int)$orders[1]->id, 2);
    $this->assertEquals((int)$orders[2]->id, 5);
    $this->assertEquals((int)$orders[3]->id, 6);
    
    //------------------------------------------------------
    
    $cu->setChildConstraint('customer_order',
                            array('limit' => 10, 'order' => 'id desc'));
                            
    $cu->getChild('customer_order');
    
    $orders = $cu->customer_order;
    $this->assertEquals(count($orders), 4);
    
    $this->assertEquals((int)$orders[0]->id, 6);
    $this->assertEquals((int)$orders[1]->id, 5);
    $this->assertEquals((int)$orders[2]->id, 2);
    $this->assertEquals((int)$orders[3]->id, 1);
    
    //------------------------------------------------------
    
    $cu->setChildConstraint('customer_order',
                            array('limit'  => 2, 'offset' => 2, 'order'  => 'id desc'));
                            
    $cu->getChild('customer_order');
    
    $orders = $cu->customer_order;
    $this->assertEquals(count($orders), 2);
    
    $this->assertEquals((int)$orders[0]->id, 2);
    $this->assertEquals((int)$orders[1]->id, 1);
    $this->assertEquals($orders[2]->id, null);
  }
  
  public function testNewChild()
  {
    $cu = new Customer();
    $cu->setChildConstraint(array('limit' => 10));
    $c  = $cu->selectOne(1);
    $ch = $c->newChild('customer_order');
    
    $ch->id = 7;
    $ch->save();  // auto insert parent_id
    
    $co = new Customer_Order();
    $co->setChildConstraint(array('limit' => 10));
    $order = $co->selectOne($number);
    $this->assertEquals((int)$order->customer_id, 1);  // parent_id
  }
  
  public function testSelectAll_AutoGetChild()
  {
    $cu   = new Customer();
    $cu->setChildConstraint(array('limit' => 10));
    $objs = $cu->select();
    
    $this->assertEquals(count($objs), 2);
    $this->assertNotEquals($objs[0]->customer_order, null);
    $this->assertNotEquals($objs[1]->customer_order, null);
    
    $this->assertEquals(count($objs[0]->customer_order), 5);
    $this->assertEquals(count($objs[1]->customer_order), 2);
    
    $this->assertEquals((int)$objs[0]->customer_order[0]->id, 1);
    $this->assertEquals((int)$objs[0]->customer_order[1]->id, 2);
    $this->assertEquals((int)$objs[1]->customer_order[0]->id, 3);
    $this->assertEquals((int)$objs[1]->customer_order[1]->id, 4);
    $this->assertEquals((int)$objs[0]->customer_order[2]->id, 5);
    $this->assertEquals((int)$objs[0]->customer_order[3]->id, 6);
    $this->assertEquals((int)$objs[0]->customer_order[4]->id, 7);
    
    //-------------------------------------------------------
    
    $cu   = new Customer();
    $cu->setChildConstraint(array('limit' => 10,
                                  'order' => 'id desc'));
    $objs = $cu->select();
    
    $this->assertEquals((int)$objs[0]->customer_order[0]->id, 7);
    $this->assertEquals((int)$objs[0]->customer_order[1]->id, 6);
    $this->assertEquals((int)$objs[0]->customer_order[2]->id, 5);
    $this->assertEquals((int)$objs[1]->customer_order[0]->id, 4);
    $this->assertEquals((int)$objs[1]->customer_order[1]->id, 3);
    $this->assertEquals((int)$objs[0]->customer_order[3]->id, 2);
    $this->assertEquals((int)$objs[0]->customer_order[4]->id, 1);
    
    $this->assertEquals((int)$objs[0]->customer_order[4]->order_line[0]->id, 11);
    $this->assertEquals((int)$objs[0]->customer_order[4]->order_line[1]->id, 8);
    $this->assertEquals((int)$objs[0]->customer_order[4]->order_line[2]->id, 2);
    
    //-------------------------------------------------------
    
    $cu   = new Customer();
    $cu->setChildConstraint(array('limit' => 10));
    $objs = $cu->select();
    $this->assertNotEquals((int)$objs[0]->customer_order[0]->order_line, null);
    $this->assertNotEquals((int)$objs[1]->customer_order[0]->order_line, null);
    
    $this->assertEquals((int)$objs[0]->customer_order[0]->order_line[0]->id, 2);
    $this->assertEquals((int)$objs[0]->customer_order[0]->order_line[1]->id, 8);
    $this->assertEquals((int)$objs[0]->customer_order[0]->order_line[2]->id, 11);
    $this->assertEquals($objs[0]->customer_order[0]->order_line[3]->id, null);  // hasn't
    
    $this->assertEquals((int)$objs[0]->customer_order[1]->order_line[0]->id, 3);
    $this->assertEquals((int)$objs[0]->customer_order[1]->order_line[0]->item_id, 3);
    $this->assertEquals((int)$objs[0]->customer_order[1]->order_line[1]->id, 4);
    $this->assertEquals((int)$objs[0]->customer_order[1]->order_line[1]->item_id, 1);
    $this->assertEquals($objs[0]->customer_order[1]->order_line[2]->id, null);  // hasn't
    
    $this->assertEquals((int)$objs[0]->customer_order[2]->order_line[0]->id, 1);
    $this->assertEquals((int)$objs[0]->customer_order[2]->order_line[0]->item_id, 2);
    $this->assertEquals((int)$objs[0]->customer_order[2]->order_line[1]->id, 7);
    $this->assertEquals((int)$objs[0]->customer_order[2]->order_line[1]->item_id, 3);
    $this->assertEquals($objs[0]->customer_order[2]->order_line[2]->id, null);  // hasn't
    
    $this->assertEquals((int)$objs[1]->customer_order[0]->order_line[0]->id, 6);
    $this->assertEquals((int)$objs[1]->customer_order[0]->order_line[0]->item_id, 2);
    $this->assertEquals($objs[1]->customer_order[0]->order_line[1]->id, null);  // hasn't
    $this->assertEquals((int)$objs[1]->customer_order[1]->order_line[0]->id, 5);
    $this->assertEquals((int)$objs[1]->customer_order[1]->order_line[0]->item_id, 3);
    $this->assertEquals($objs[1]->customer_order[1]->order_line[1]->id, null);  // hasn't
    
    $this->assertEquals((int)$objs[0]->customer_telephone[0]->id, 1);
    $this->assertEquals((int)$objs[0]->customer_telephone[1]->id, 3);
    $this->assertEquals((int)$objs[1]->customer_telephone[0]->id, 2);
    $this->assertEquals((int)$objs[1]->customer_telephone[1]->id, 4);
    
    $this->assertEquals($objs[0]->customer_telephone[0]->telephone, '09011111111');
    $this->assertEquals($objs[0]->customer_telephone[1]->telephone, '09011112222');
    $this->assertEquals($objs[1]->customer_telephone[0]->telephone, '09022221111');
    $this->assertEquals($objs[1]->customer_telephone[1]->telephone, '09022222222');
    
    //--------------------------------------------------------------------
    
    $objs[0]->clearChild('customer_telephone');
    
    $cu   = new Customer();
    $cu->setChildConstraint(array('limit' => 10));
    $objs = $cu->select();
    
    $this->assertEquals($objs[0]->customer_telephone[0]->telephone, null);
    $this->assertEquals($objs[0]->customer_telephone[1]->telephone, null);
    $this->assertEquals($objs[1]->customer_telephone[0]->telephone, '09022221111');
    $this->assertEquals($objs[1]->customer_telephone[1]->telephone, '09022222222');
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
  
  public function testAggregate()
  {
    $order = new Customer_Order();
    $order->setConstraint('order', 'sum_amount desc');
    
    $function = array('sum'   => 'amount',
                      'avg'   => 'amount',
                      'count' => 'amount');
                      
    $result = $order->aggregate($function, 'order_line');
    
    $this->assertEquals((int)$result[0]->sum_amount, 60000);
    $this->assertEquals((int)$result[1]->sum_amount, 13000);
    $this->assertEquals((int)$result[2]->sum_amount, 9000);
    $this->assertEquals((int)$result[3]->sum_amount, 6500);
    $this->assertEquals((int)$result[4]->sum_amount, 3500);
    $this->assertEquals((int)$result[5]->sum_amount, 1500);
    
    $this->assertEquals((int)$result[0]->count_amount, 2);
    $this->assertEquals((int)$result[0]->avg_amount,   30000);
  }
  
  public function testSeq()
  {
    $seq = new Sabel_Edo_CommonRecord('seq');
    
    $seq->text = 'test';
    $id = $seq->save();
  }

  public function testTest()
  {
    class_exists('TestTestTes');
  }
  
  public function testTree()
  {
    $tree  = new Tree();
    $trees = $tree->select();
    $this->assertEquals((int)$trees[0]->id, 1);
    $this->assertEquals($trees[0]->tree_id, null);
    $this->assertEquals($trees[0]->name, 'A');
    
    $t = $tree->selectOne(1);
    $this->assertEquals((int)$t->id, 1);
    $this->assertEquals($t->tree_id, null);
    $this->assertEquals($t->name, 'A');
    
    $t->setChildConstraint(array('limit' => 100));
    $t->getChild('tree');
    
    $this->assertEquals(count($t->tree), 2);
    $this->assertEquals((int)$t->tree[0]->id, 3);
    $this->assertEquals((int)$t->tree[1]->id, 5);
    $this->assertEquals((int)$t->tree[0]->tree_id, 1);
    $this->assertEquals((int)$t->tree[1]->tree_id, 1);
    $this->assertEquals($t->tree[0]->name, 'A3');
    $this->assertEquals($t->tree[1]->name, 'A5');
    
    $tree = new Tree();
    $tree->setSelectType(Sabel_Edo_RecordObject::WITH_PARENT_OBJECT);

    $t = $tree->selectOne(3);
    
    $this->assertEquals((int)$t->id, 3);
    $this->assertEquals((int)$t->tree_id, 1);
    $this->assertEquals($t->name, 'A3');
    
    $this->assertEquals((int)$t->tree->id, 1);
    $this->assertEquals((int)$t->tree->tree_id, 0);
    $this->assertEquals($t->tree->name, 'A');

    $t = $tree->selectOne(5);
    $this->assertEquals((int)$t->id, 5);
    $this->assertEquals((int)$t->tree_id, 1);
    $this->assertEquals($t->name, 'A5');

    $this->assertEquals((int)$t->tree->id, 1);
    $this->assertEquals((int)$t->tree->tree_id, 0);
    $this->assertEquals($t->tree->name, 'A');
  }

  public function testBridge()
  {
    $stu = new Student(1);
    $this->assertEquals($stu->name, 'tom');
    $this->assertEquals($stu->student_course, null);

    $constraint = array('limit' => 100, 'order' => 'course_id');
    $stu->setChildConstraint('student_course', $constraint);

    $stu->getChild('course', 'student_course');

    $this->assertEquals((int)$stu->student_course[0]->course_id, 1);
    $this->assertEquals((int)$stu->student_course[1]->course_id, 2);
    $this->assertEquals((int)$stu->student_course[2]->course_id, 3);

    $this->assertEquals((int)$stu->course[0]->id, 1);
    $this->assertEquals((int)$stu->course[1]->id, 2);
    $this->assertEquals((int)$stu->course[2]->id, 3);

    $this->assertEquals($stu->course[0]->name, 'Mathematics');
    $this->assertEquals($stu->course[1]->name, 'Physics');
    $this->assertEquals($stu->course[2]->name, 'Science');

    $stu = new Student();
    $stu->setConstraint('order', 'id desc');

    $students = $stu->select();
    $this->assertEquals((int)$students[0]->id, 5);
    $this->assertEquals((int)$students[1]->id, 4);

    $constraint = array('limit' => 100, 'order' => 'course_id desc');

    foreach ($students as $student) {
      $student->setChildConstraint($constraint);
      $student->getChild('course', 'student_course');
    }

    $this->assertEquals((int)$students[2]->student_course[0]->course_id, 5);
    $this->assertEquals((int)$students[2]->student_course[1]->course_id, 4);
    $this->assertEquals((int)$students[2]->student_course[2]->course_id, 2);
    $this->assertEquals((int)$students[2]->student_course[3]->course_id, 1);

    $this->assertEquals((int)$students[3]->course[0]->id, 5);
    $this->assertEquals((int)$students[3]->course[1]->id, 4);
    $this->assertEquals((int)$students[3]->course[2]->id, 3);
    $this->assertEquals((int)$students[3]->course[3]->id, 2);

    $this->assertEquals($students[3]->course[0]->name, 'Psychology');
    $this->assertEquals($students[3]->course[1]->name, 'Economic');
    $this->assertEquals($students[3]->course[2]->name, 'Science');
    $this->assertEquals($students[3]->course[3]->name, 'Physics');

    //-----------------------------------------------------------------

    $course = new Course();
    $course->setConstraint('order', 'id');

    $cs = $course->select();

    $constraint = array('limit' => 100, 'order' => 'student_id');

    foreach ($cs as $c) {
      $c->setChildConstraint($constraint);
      $c->getChild('student', 'student_course');
    }

    $this->assertEquals($cs[0]->student[0]->name, 'tom');
    $this->assertEquals($cs[0]->student[1]->name, 'bob');
    $this->assertEquals($cs[0]->student[2]->name, 'ameri');

    $this->assertEquals($cs[2]->student[0]->name, 'tom');
    $this->assertEquals($cs[2]->student[1]->name, 'john');
    $this->assertEquals($cs[2]->student[2]->name, 'marcy');
    $this->assertEquals($cs[2]->student[3]->name, 'ameri');
  }

  public function testInherit()
  {
    $u1 = new TestUser1();
    $u2 = new TestUser2();

    $this->assertEquals($u1->testGetTableName(), $u2->testGetTableName());
    $this->assertEquals($u1->testGetTableName(), 'users');

    $this->assertNotEquals($u1->getMyClassName(), $u2->getMyClassName());
    $this->assertEquals($u1->getMyClassName(), 'TestUser1');
    $this->assertEquals($u2->getMyClassName(), 'TestUser2');

    $users1 = $u1->select();
    $users2 = $u2->select();
    $this->assertEquals($users1[0]->name, 'Tarou');
    $this->assertEquals($users1[1]->name, 'Hanako');
    $this->assertEquals($users2[0]->name, 'Tarou');
    $this->assertEquals($users2[1]->name, 'Hanako');
  }
}

class MysqlHelper
{
  protected $sqls = null;
  
  protected $tables = array('test', 'test2', 'test3',
                            'customer', 'customer_order', 'order_line',
                            'customer_telephone', 'infinite1', 'infinite2',
                            'seq', 'tree', 'student', 'student_course',
                            'course', 'users');
  
  public function __construct()
  {
    $dbCon = array();
    $dbCon['dsn']  = 'mysql:host=localhost;dbname=edo';
    $dbCon['user'] = 'root';
    $dbCon['pass'] = '';
    
    Sabel_Edo_DBConnection::addConnection('user', 'pdo', $dbCon);
    
    $SQLs = array();
    
    $SQLs[] = 'CREATE TABLE test (
                 id       INT2 PRIMARY KEY,
                 name     VARCHAR(32) NOT NULL,
                 blood    VARCHAR(32),
                 test2_id INT2)';
    
    $SQLs[] = 'CREATE TABLE test2 (
                 id int2 PRIMARY KEY,
                 name VARCHAR(32) NOT NULL,
                 test3_id int2)';
                 
    $SQLs[] = 'CREATE TABLE test3 (
                id INT2 PRIMARY KEY,
                name VARCHAR(32) NOT NULL)';
                
    $SQLs[] = 'CREATE TABLE customer (
                id INT2 PRIMARY KEY,
                name VARCHAR(32) NOT NULL)';
                
    $SQLs[] = 'CREATE TABLE customer_order (
                id INT2 PRIMARY KEY,
                customer_id INT2 NOT NULL)';
    
    $SQLs[] = 'CREATE TABLE order_line (
                id INT2 PRIMARY KEY,
                customer_order_id INT2 NOT NULL,
                amount INT4 NOT NULL,
                item_id INT2 NOT NULL)';
                
    $SQLs[] = 'CREATE TABLE customer_telephone (
                id INT2 PRIMARY KEY,
                customer_id INT2 NOT NULL,
                telephone VARCHAR(32))';
                
    $SQLs[] = 'CREATE TABLE infinite1 (
                id INT2 PRIMARY KEY,
                infinite2_id INT2 NOT NULL)';
                
    $SQLs[] = 'CREATE TABLE infinite2 (
                id INT2 PRIMARY KEY,
                infinite1_id int2 NOT NULL)';
                
    $SQLs[] = 'CREATE TABLE seq (
                 id INT2 PRIMARY KEY AUTO_INCREMENT,
                 text VARCHAR(65536) NOT NULL)';
    
    $SQLs[] = 'CREATE TABLE tree (
                 id INT2 PRIMARY KEY,
                 tree_id INT2,
                 name VARCHAR(12) )';

    $SQLs[] = 'CREATE TABLE student (
                 id INT4 PRIMARY KEY AUTO_INCREMENT,
                 name VARCHAR(24) NOT NULL,
                 birth DATE)';
    
    $SQLs[] = 'CREATE TABLE student_course (
                 student_id INT4 NOT NULL,
                 course_id INT4 NOT NULL,
                 CONSTRAINT student_course_pkey PRIMARY KEY (student_id, course_id) )';

    $SQLs[] = 'CREATE TABLE course (
                 id INT4 PRIMARY KEY AUTO_INCREMENT,
                 name VARCHAR(24) )';
                
    $SQLs[] = 'CREATE TABLE users (
                 id INT4 PRIMARY KEY AUTO_INCREMENT,
                 name VARCHAR(24) NOT NULL,
                 age INT2 NOT NULL,
                 location VARCHAR(24) )';

    $this->sqls = $SQLs;
  }
  
  public function createTables()
  {
    $obj = new Sabel_Edo_CommonRecord();
    
    foreach ($this->sqls as $sql) {
      $obj->execute($sql);
    }
  }
  
  public function dropTables()
  {
    $obj = new Sabel_Edo_CommonRecord();
    
    foreach ($this->tables as $table) {
      $obj->execute("DROP TABLE ${table}");
    }
  }
}

class PgsqlHelper
{
  protected $sqls = null;

  protected $tables = array('test', 'test2', 'test3',
                            'customer', 'customer_order', 'order_line',
                            'customer_telephone', 'infinite1', 'infinite2',
                            'seq', 'tree', 'student', 'student_course',
                            'course', 'users');
                            
  public function __construct()
  {
    $dbCon = array();
    $dbCon['dsn']  = 'pgsql:host=localhost;dbname=edo';
    $dbCon['user'] = 'pgsql';
    $dbCon['pass'] = 'pgsql';
    Sabel_Edo_DBConnection::addConnection('user', 'pdo', $dbCon);

    //$dbCon = pg_connect("host=localhost dbname=edo user=pgsql password=pgsql");
    //Sabel_Edo_DBConnection::addConnection('user', 'pgsql', $dbCon);
    
    $SQLs = array();
    
    $SQLs[] = 'CREATE TABLE test (
                 id       SERIAL PRIMARY KEY,
                 name     VARCHAR(32) NOT NULL,
                 blood    VARCHAR(32),
                 test2_id INT2)';
    
    $SQLs[] = 'CREATE TABLE test2 (
                 id       SERIAL PRIMARY KEY,
                 name     VARCHAR(32) NOT NULL,
                 test3_id INT2)';
                 
    $SQLs[] = 'CREATE TABLE test3 (
                id   SERIAL PRIMARY KEY,
                name VARCHAR(32) NOT NULL)';
                
    $SQLs[] = 'CREATE TABLE customer (
                id   SERIAL PRIMARY KEY,
                name VARCHAR(32) NOT NULL)';
                
    $SQLs[] = 'CREATE TABLE customer_order (
                id          SERIAL PRIMARY KEY,
                customer_id INT2 NOT NULL)';
    
    $SQLs[] = 'CREATE TABLE order_line (
                id                SERIAL PRIMARY KEY,
                customer_order_id INT2 NOT NULL,
                amount            INT4 NOT NULL,
                item_id           INT2 NOT NULL)';
                
    $SQLs[] = 'CREATE TABLE customer_telephone (
                id SERIAL   PRIMARY KEY,
                customer_id INT2 NOT NULL,
                telephone   VARCHAR(32))';
                
    $SQLs[] = 'CREATE TABLE infinite1 (
                id           SERIAL PRIMARY KEY,
                infinite2_id INT2 NOT NULL)';
                
    $SQLs[] = 'CREATE TABLE infinite2 (
                id           SERIAL PRIMARY KEY,
                infinite1_id int2 NOT NULL)';
                
    $SQLs[] = 'CREATE TABLE seq (
                 id   SERIAL PRIMARY KEY,
                 text VARCHAR(65536) NOT NULL)';
    
    $SQLs[] = 'CREATE TABLE tree (
                 id      SERIAL PRIMARY KEY,
                 tree_id INT2,
                 name    VARCHAR(12) )';
                
    $SQLs[] = 'CREATE TABLE student (
                 id    SERIAL PRIMARY KEY,
                 name  VARCHAR(24) NOT NULL,
                 birth DATE)';
    
    $SQLs[] = 'CREATE TABLE student_course (
                 student_id INT4 NOT NULL,
                 course_id  INT4 NOT NULL,
                 CONSTRAINT student_course_pkey PRIMARY KEY (student_id, course_id) )';

    $SQLs[] = 'CREATE TABLE course (
                 id   SERIAL PRIMARY KEY,
                 name VARCHAR(24) )';
                
    $SQLs[] = 'CREATE TABLE users (
                 id       SERIAL PRIMARY KEY,
                 name     VARCHAR(24) NOT NULL,
                 age      INT2 NOT NULL,
                 location VARCHAR(24) )';

    $this->sqls = $SQLs;
  }
  
  public function createTables()
  {
    $obj = new Sabel_Edo_CommonRecord();
    
    foreach ($this->sqls as $sql) {
      $obj->execute($sql);
    }
  }
  
  public function dropTables()
  {
    $obj = new Sabel_Edo_CommonRecord();
    
    foreach ($this->tables as $table) {
      $obj->execute("DROP TABLE ${table}");
    }
  }
}

//-----------------------------------------------------------------

abstract class BaseUserRecordObject extends Sabel_Edo_RecordObject
{
  protected $myChildren         = null;
  protected $myChildConstraints = array();

  public function __construct($param1 = null, $param2 = null)
  {
    $this->setEDO('user');
    parent::__construct($param1, $param2);
  }

  public function getMyChildren()
  {
    return $this->myChildren;
  }

  public function getMyChildConstraint()
  {
    return $this->myChildConstraints;
  }
}

class Test extends BaseUserRecordObject
{
  protected $selectType = Sabel_Edo_RecordObject::WITH_PARENT_OBJECT;

  public function getCondition()
  {
    return $this->conditions;
  }

  public function unsetCondition()
  {
    $this->conditions = array();
  }

  public function getData()
  {
    return $this->data;
  }
}

class Customer extends BaseUserRecordObject
{
  protected $myChildren = array('customer_order','customer_telephone');
  protected $defaultChildConstraints = array('limit' => 10); // (for telephone)

  public function __construct($param1 = null, $param2 = null)
  {
    $this->myChildConstraints['customer_order'] = array('limit' => 10);
    parent::__construct($param1, $param2);
  }
}

class Customer_Order extends BaseUserRecordObject
{
  protected $myChildren = 'order_line';

  public function __construct($param1 = null, $param2 = null)
  {
    $this->myChildConstraints['order_line'] = array('limit' => 10);
    parent::__construct($param1, $param2);
  }
}

class Tree extends BaseTreeRecord
{

}

class Student extends BaseBridgeRecord
{

}

class Course extends BaseBridgeRecord
{

}

class Users extends BaseUserRecordObject
{
  public function __construct($param1 = null, $param2 = null)
  {
    $this->table = 'users';
    parent::__construct($param1, $param2);
  }
}

class TestUser1 extends Users
{
  public function __construct($param1 = null, $param2 = null)
  {
    parent::__construct($param1, $param2);
  }

  public function getMyClassName()
  {
    return get_class($this);
  }

  public function testGetTableName()
  {
    return $this->table;
  }
}

class TestUser2 extends Users
{
  public function __construct($param1 = null, $param2 = null)
  {
    parent::__construct($param1, $param2);
  }

  public function getMyClassName()
  {
    return get_class($this);
  }

  public function testGetTableName()
  {
    return $this->table;
  }
}

if (PHPUnit2_MAIN_METHOD == "Test_Edo::main") {
    Test_Edo::main();
}
