<?php

if (!defined("PHPUnit2_MAIN_METHOD")) {
    define("PHPUnit2_MAIN_METHOD", "Test_Edo::main");
}

require_once "sabel/db/driver/Interface.php";
require_once "sabel/db/query/Interface.php";
require_once "sabel/db/query/Factory.php";

require_once "sabel/db/Mapper.php";
require_once "sabel/db/BaseClasses.php";

require_once "sabel/db/Transaction.php";
require_once "sabel/db/schema/Accessor.php";
require_once "sabel/db/Connection.php";

require_once "sabel/db/driver/Pdo.php";
require_once "sabel/db/driver/Pgsql.php";
require_once "sabel/db/driver/Mysql.php";

/**
 * test for Sabel_DB
 *
 * @author Ebine Yutaka <ebine.yutaka@gmail.com>
 */
class Test_Edo extends SabelTestCase
{
  public static function main() {
    require_once "PHPUnit2/TextUI/TestRunner.php";

    $suite  = new PHPUnit2_Framework_TestSuite("Test_Edo");
    $result = PHPUnit2_TextUI_TestRunner::run($suite);
  }

  public static function suite()
  {
    $helper = new MysqlHelper();
    //$helper = new PgsqlHelper();
    //$helper = new SQLiteHelper();

    try {
      $helper->dropTables();
    } catch (Exception $e) {
      print_r($e);
    }

    $helper->createTables();
    return new PHPUnit2_Framework_TestSuite("Test_Edo");
  }

  public function __construct()
  {

  }

  protected function setUp()
  {
    $this->test      = new Test();
    $this->test2     = new Sabel_DB_Basic('test2');
    $this->test3     = new Sabel_DB_Basic('test3');
    $this->customer  = new Customer();
    $this->order     = new Sabel_DB_Basic('customer_order');
    $this->orderLine = new Sabel_DB_Basic('order_line');
    $this->telephone = new Sabel_DB_Basic('customer_telephone');
  }

  protected function tearDown()
  {
  }

  public function testConstraint()
  {
    $insertData   = array();
    $insertData[] = array('id' => 1, 'name' => 'tanaka');
    $insertData[] = array('id' => 2, 'name' => 'ueda');
    $this->customer->multipleInsert($insertData);

    $this->assertEquals($this->customer->getCount(), 2);

    $insertData   = array();
    $insertData[] = array('id' => 1, 'customer_id' => 1);
    $insertData[] = array('id' => 2, 'customer_id' => 1);
    $insertData[] = array('id' => 3, 'customer_id' => 2);
    $insertData[] = array('id' => 4, 'customer_id' => 2);
    $insertData[] = array('id' => 5, 'customer_id' => 1);
    $insertData[] = array('id' => 6, 'customer_id' => 1);
    $this->order->multipleInsert($insertData);

    $o = new Sabel_DB_Basic('customer_order');
    $res = $o->select(Sabel_DB_Mapper::WITH_PARENT);
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
    @$this->assertNull($cus[0]->customer_order[2]->id);
    @$this->assertNull($cus[0]->customer_order[3]->id);
  }

  public function testMultipleInsert()
  {
    $insertData = array();
    $insertData[] = array('id' => 1, 'name' => 'tanaka',   'blood' => 'A',  'test2_id' => 1);
    $insertData[] = array('id' => 2, 'name' => 'yo_shida', 'blood' => 'B',  'test2_id' => 2);
    $insertData[] = array('id' => 3, 'name' => 'uchida',   'blood' => 'AB', 'test2_id' => 1);
    $insertData[] = array('id' => 4, 'name' => 'ueda',     'blood' => 'A',  'test2_id' => 3);
    $insertData[] = array('id' => 5, 'name' => 'seki',     'blood' => 'O',  'test2_id' => 4);
    $insertData[] = array('id' => 6, 'name' => 'uchida',   'blood' => 'A',  'test2_id' => 1);
    $this->test->multipleInsert($insertData);
    
    $ro = $this->test->select();
    $this->assertEquals(count($ro), 6);
    
    $this->test->enableParent();
    $obj = $this->test->selectOne(5);
    $this->assertEquals($obj->name, 'seki');
    $this->assertEquals((int)$obj->test2->id, 4);
    
    $obj = $this->test->selectOne('name', 'seki');
    $this->assertEquals((int)$obj->id, 5);

    $insertData = array();
    $insertData[] = array('customer_order_id' => 5, 'amount' => 1000,  'item_id' => 2);
    $insertData[] = array('customer_order_id' => 1, 'amount' => 3000,  'item_id' => 1);
    $insertData[] = array('customer_order_id' => 2, 'amount' => 5000,  'item_id' => 3);
    $insertData[] = array('customer_order_id' => 2, 'amount' => 8000,  'item_id' => 1);
    $insertData[] = array('customer_order_id' => 4, 'amount' => 9000,  'item_id' => 3);
    $insertData[] = array('customer_order_id' => 3, 'amount' => 1500,  'item_id' => 2);
    $insertData[] = array('customer_order_id' => 5, 'amount' => 2500,  'item_id' => 3);
    $insertData[] = array('customer_order_id' => 1, 'amount' => 3000,  'item_id' => 1);
    $insertData[] = array('customer_order_id' => 6, 'amount' => 10000, 'item_id' => 1);
    $insertData[] = array('customer_order_id' => 6, 'amount' => 50000, 'item_id' => 2);
    $insertData[] = array('customer_order_id' => 1, 'amount' => 500,   'item_id' => 3);
    $this->orderLine->multipleInsert($insertData);
    $this->assertEquals($this->orderLine->getCount(), 11);
    
    $insertData   = array();
    $insertData[] = array('id' => 1,  'customer_id' => 1, 'telephone' => '09011111111');
    $insertData[] = array('id' => 2,  'customer_id' => 2, 'telephone' => '09022221111');
    $insertData[] = array('id' => 3,  'customer_id' => 1, 'telephone' => '09011112222');
    $insertData[] = array('id' => 4,  'customer_id' => 2, 'telephone' => '09022222222');
    $this->telephone->multipleInsert($insertData);

    $this->assertEquals($this->orderLine->getCount(), 11);
    
    $tree = new Sabel_DB_Basic('tree');
    $insertData   = array();
    $insertData[] = array('id' => 1,  'name' => 'A');
    $insertData[] = array('id' => 2,  'name' => 'B');
    $insertData[] = array('id' => 3,  'tree_id' => 1, 'name' => 'A3');
    $insertData[] = array('id' => 4,  'name' => 'C');
    $insertData[] = array('id' => 5,  'tree_id' => 1, 'name' => 'A5');
    $insertData[] = array('id' => 6,  'tree_id' => 2, 'name' => 'B6');
    $insertData[] = array('id' => 7,  'tree_id' => 3, 'name' => 'A3-7');
    $insertData[] = array('id' => 8,  'tree_id' => 4);
    $insertData[] = array('id' => 9,  'tree_id' => 2, 'name' => 'B9');
    $insertData[] = array('id' => 10, 'tree_id' => 6, 'name' => 'B6-10');
    $insertData[] = array('id' => 11, 'tree_id' => 4, 'name' => 'C11');
    $tree->multipleInsert($insertData);

    $student = new Sabel_DB_Basic('student');
    $insertData   = array();
    $insertData[] = array('name' => 'tom',   'birth' => '1983/08/17');
    $insertData[] = array('name' => 'john',  'birth' => '1983/08/18');
    $insertData[] = array('name' => 'bob',   'birth' => '1983/08/19');
    $insertData[] = array('name' => 'marcy', 'birth' => '1983/08/20');
    $insertData[] = array('name' => 'ameri', 'birth' => '1983/08/21');
    $student->multipleInsert($insertData);

    $course = new Sabel_DB_Basic('course');
    $insertData   = array();
    $insertData[] = array('name' => 'Mathematics');
    $insertData[] = array('name' => 'Physics');
    $insertData[] = array('name' => 'Science');
    $insertData[] = array('name' => 'Economic');
    $insertData[] = array('name' => 'Psychology');
    $course->multipleInsert($insertData);

    $sc = new Sabel_DB_Basic('student_course');
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
    $sc->multipleInsert($insertData);

    $users = new Users();
    $insertData   = array();
    $insertData[] = array('name' => 'Tarou'  , 'status_id' => 1);
    $insertData[] = array('name' => 'Hanako' , 'status_id' => 2);
    $insertData[] = array('name' => 'Maruo'  , 'status_id' => 1);
    $insertData[] = array('name' => 'Atsuko' , 'status_id' => 1);
    $users->multipleInsert($insertData);

    $s = new Sabel_DB_Basic('status');
    $s->state = 'normal';
    $s->save();
    $s->state = 'invalid';
    $s->save();

    $s  = new Sabel_DB_Basic('status');
    $ss = $s->select();

    $this->assertEquals((int)$ss[0]->id, 1);
    $this->assertEquals($ss[0]->state, 'normal');

    $this->assertEquals((int)$ss[1]->id, 2);
    $this->assertEquals($ss[1]->state, 'invalid');

    $bbs = new Sabel_DB_Basic('bbs');

    $insertData   = array();
    $insertData[] = array('users_id' => 1 , 'title' => 'title11', 'body' => 'body11');
    $insertData[] = array('users_id' => 1 , 'title' => 'title12', 'body' => 'body12');
    $insertData[] = array('users_id' => 1 , 'title' => 'title13', 'body' => 'body13');
    $insertData[] = array('users_id' => 1 , 'title' => 'title14', 'body' => 'body14');
    $insertData[] = array('users_id' => 1 , 'title' => 'title15', 'body' => 'body15');

    $insertData[] = array('users_id' => 2 , 'title' => 'title21', 'body' => 'body21');
    $insertData[] = array('users_id' => 2 , 'title' => 'title22', 'body' => 'body22');
    $insertData[] = array('users_id' => 2 , 'title' => 'title23', 'body' => 'body23');
    $insertData[] = array('users_id' => 2 , 'title' => 'title24', 'body' => 'body24');
    $insertData[] = array('users_id' => 2 , 'title' => 'title25', 'body' => 'body25');

    $insertData[] = array('users_id' => 3 , 'title' => 'title31', 'body' => 'body31');
    $insertData[] = array('users_id' => 3 , 'title' => 'title32', 'body' => 'body32');
    $insertData[] = array('users_id' => 3 , 'title' => 'title33', 'body' => 'body33');
    $insertData[] = array('users_id' => 3 , 'title' => 'title34', 'body' => 'body34');
    $insertData[] = array('users_id' => 3 , 'title' => 'title35', 'body' => 'body35');

    $insertData[] = array('users_id' => 4 , 'title' => 'title41', 'body' => 'body41');
    $insertData[] = array('users_id' => 4 , 'title' => 'title42', 'body' => 'body42');
    $insertData[] = array('users_id' => 4 , 'title' => 'title43', 'body' => 'body43');
    $insertData[] = array('users_id' => 4 , 'title' => 'title44', 'body' => 'body44');
    $insertData[] = array('users_id' => 4 , 'title' => 'title45', 'body' => 'body45');
    $bbs->multipleInsert($insertData);
  }

  public function testInsert()
  {
    $test2 = new Sabel_DB_Basic('test2');
    $test2->id   = 1;
    $test2->name = 'test21';
    $test2->test3_id = '2';
    $test2->save();

    $test2 = new Sabel_DB_Basic('test2');
    $test2->id   = 2;
    $test2->name = 'test22';
    $test2->test3_id = '1';
    $test2->save();
    
    $test2 = new Sabel_DB_Basic('test2');
    $test2->id   = 3;
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

    $t = new Test(99);
    $this->assertEquals($t->is_selected(), false);
    $t->name     = 'test99';
    $t->blood    = 'C';
    $t->test2_id = '3';
    $t->save();

    $t = new Test(99);
    $this->assertEquals($t->is_selected(), true);
    $t->remove();
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
    $test->setProjection(array('id', 'blood'));
    
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
    @$this->assertNull($obj[2]->name);

    $this->test->OR_id('<2', '>5');
    $obj = $this->test->select();
    $this->assertEquals((int) $obj[0]->id, 1);
    $this->assertEquals((int) $obj[1]->id, 6);
  }

  public function testInfiniteLoop()
  {
    $in1 = new Sabel_DB_Basic('infinite1');
    $in1->id           = 1;
    $in1->infinite2_id = 2;
    $in1->save();
    
    $in2 = new Sabel_DB_Basic('infinite2');
    $in2->id           = 2;
    $in2->infinite1_id = 1;
    $in2->save();
    
    $objs = $in1->select(Sabel_DB_Mapper::WITH_PARENT);
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
  
  public function testGetChild()
  {
    $this->customer->setChildConstraint('limit', 10);
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
    @$this->assertNull($orders[2]->id);
  }
  
  public function testNewChild()
  {
    $cu = new Customer();
    $cu->setChildConstraint('limit', 10);
    $c  = $cu->selectOne(1);
    $ch = $c->newChild('customer_order');
    
    $ch->id = 7;
    $ch->save();  // auto insert parent_id
    
    $co = new Customer_Order();
    $co->setChildConstraint('limit', 10);
    $order = $co->selectOne(7);
    $this->assertEquals((int)$order->customer_id, 1);  // parent_id
  }

  public function testSelectAll_AutoGetChild()
  {
    $cu   = new Customer();
    $cu->setChildConstraint('limit', 10);
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
                                  'order' => 'id desc')); // default: for telephone & order_line

    $cu->setChildConstraint('customer_order', array('order' => 'id desc'));
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
    $cu->setChildConstraint('limit', 10);
    $objs = $cu->select();
    $this->assertNotEquals((int)$objs[0]->customer_order[0]->order_line, null);
    $this->assertNotEquals((int)$objs[1]->customer_order[0]->order_line, null);
    
    $this->assertEquals((int)$objs[0]->customer_order[0]->order_line[0]->id, 2);
    $this->assertEquals((int)$objs[0]->customer_order[0]->order_line[1]->id, 8);
    $this->assertEquals((int)$objs[0]->customer_order[0]->order_line[2]->id, 11);
    @$this->assertNull($objs[0]->customer_order[0]->order_line[3]->id);  // hasn't
    
    $this->assertEquals((int)$objs[0]->customer_order[1]->order_line[0]->id, 3);
    $this->assertEquals((int)$objs[0]->customer_order[1]->order_line[0]->item_id, 3);
    $this->assertEquals((int)$objs[0]->customer_order[1]->order_line[1]->id, 4);
    $this->assertEquals((int)$objs[0]->customer_order[1]->order_line[1]->item_id, 1);
    @$this->assertNull($objs[0]->customer_order[1]->order_line[2]->id);  // hasn't
    
    $this->assertEquals((int)$objs[0]->customer_order[2]->order_line[0]->id, 1);
    $this->assertEquals((int)$objs[0]->customer_order[2]->order_line[0]->item_id, 2);
    $this->assertEquals((int)$objs[0]->customer_order[2]->order_line[1]->id, 7);
    $this->assertEquals((int)$objs[0]->customer_order[2]->order_line[1]->item_id, 3);
    @$this->assertNull($objs[0]->customer_order[2]->order_line[2]->id);  // hasn't
    
    $this->assertEquals((int)$objs[1]->customer_order[0]->order_line[0]->id, 6);
    $this->assertEquals((int)$objs[1]->customer_order[0]->order_line[0]->item_id, 2);
    @$this->assertNull($objs[1]->customer_order[0]->order_line[1]->id);  // hasn't
    $this->assertEquals((int)$objs[1]->customer_order[1]->order_line[0]->id, 5);
    $this->assertEquals((int)$objs[1]->customer_order[1]->order_line[0]->item_id, 3);
    @$this->assertNull($objs[1]->customer_order[1]->order_line[1]->id);  // hasn't
    
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
    $cu->setChildConstraint('limit', 10);
    $objs = $cu->select();
    
    @$this->assertNull($objs[0]->customer_telephone[0]->telephone);
    @$this->assertNull($objs[0]->customer_telephone[1]->telephone);
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
    $seq = new Sabel_DB_Basic('seq');

    $seq->text = 'test';
    $id = $seq->save();

    $this->assertNotEquals($id, null);
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
    
    $t->setChildConstraint('limit', 100);
    $t->getChild('tree');
    
    $this->assertEquals(count($t->tree), 2);
    $this->assertEquals((int)$t->tree[0]->id, 3);
    $this->assertEquals((int)$t->tree[1]->id, 5);
    $this->assertEquals((int)$t->tree[0]->tree_id, 1);
    $this->assertEquals((int)$t->tree[1]->tree_id, 1);
    $this->assertEquals($t->tree[0]->name, 'A3');
    $this->assertEquals($t->tree[1]->name, 'A5');
    
    $tree = new Tree();
    $tree->enableParent();
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
 
    $t = new Tree();
    $root = $t->getRoot();
    $this->assertEquals((int)$root[0]->id, 1);
    $this->assertEquals((int)$root[1]->id, 2);
    $this->assertEquals((int)$root[2]->id, 4);
    @$this->assertNull($root[3]);

    $root[0]->setChildConstraint(array('limit' => 10));
    $root[0]->getChild('tree');
    $this->assertEquals((int)$root[0]->tree[0]->id, 3);
    $this->assertEquals((int)$root[0]->tree[1]->id, 5);

    $children = $root[0]->tree;
    $this->assertEquals((int)$children[0]->id, 3);
    $this->assertEquals($children[0]->name, 'A3');
    $this->assertEquals((int)$children[1]->id, 5);
    $this->assertEquals($children[1]->name, 'A5');
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

  public function testJoinSelect()
  {
    $users = new Users();
    $relList = array('child' => 'bbs', 'parent' => 'status');
    $users->setConstraint('order', 'users.id, bbs.title');
    $res = $users->selectJoin($relList);

    $this->assertEquals($res[0]->name, 'Tarou');
    $this->assertEquals($res[5]->name, 'Hanako');
    $this->assertEquals($res[10]->name, 'Maruo');
    $this->assertEquals($res[15]->name, 'Atsuko');

    $this->assertEquals($res[0]->status_id, $res[0]->status->id);
    $this->assertEquals($res[5]->status_id, $res[5]->status->id);
    $this->assertEquals($res[10]->status_id, $res[10]->status->id);
    $this->assertEquals($res[15]->status_id, $res[15]->status->id);

    $this->assertEquals($res[0]->bbs->title, 'title11');
    $this->assertEquals($res[1]->bbs->title, 'title12');
    $this->assertEquals($res[2]->bbs->title, 'title13');

    $users = new Users();
    $users->setConstraint('order', 'id');
    $res = $users->select(Sabel_DB_Mapper::WITH_PARENT);

    foreach ($res as $user) {
      $user->setChildConstraint('limit', 10);
      $user->getChild('bbs');
    }
    $this->assertEquals($res[0]->status_id, $res[0]->status->id);
    $this->assertEquals($res[0]->bbs[0]->title, 'title11');
    $this->assertEquals($res[0]->bbs[1]->title, 'title12');
    $this->assertEquals($res[1]->bbs[0]->title, 'title21');
    $this->assertEquals($res[1]->bbs[1]->title, 'title22');
  }

  public function testOrder()
  {
    $ol = new Sabel_DB_Basic('order_line');
    $ol->customer_order_id = 13;
    $ol->item_id = 5;
    $ol->save();

    $ol->customer_order_id = 18;
    $ol->item_id = 8;
    $ol->save();

    $last = $ol->getLast('amount');
    $this->assertEquals((int)$last->amount, 50000);

    $first = $ol->getFirst('amount');
    $this->assertEquals((int)$first->amount, 500);

    $ol = new Sabel_DB_Basic('order_line');
    $ol->item_id(3);
    $last = $ol->getLast('amount');
    $this->assertEquals((int)$last->amount, 9000);

    $ol->item_id(3);
    $first = $ol->getFirst('amount');
    $this->assertEquals((int)$first->amount, 500);

    $ol = new Sabel_DB_Basic('order_line');
    $ol->customer_order_id = 1;
    $ol->amount  = 100000;
    $ol->item_id = 1;
    $ol->save();

    $ol = new Sabel_DB_Basic('order_line');
    $ol->item_id(1);
    $ol->customer_order_id(1);
    $this->assertEquals($ol->getCount(), 3);

    $ol = new Sabel_DB_Basic('order_line');

    $ol->item_id(1);
    $ol->customer_order_id(1);
    $last = $ol->getLast('amount');
    $this->assertEquals((int)$last->amount, 100000);

    $ol->item_id(1);
    $ol->customer_order_id(1);
    $first = $ol->getFirst('amount');
    $this->assertEquals((int)$first->amount, 3000);
  }

  public function testChildCondition()
  {
    $user = new Users(1);
    $user->setChildConstraint('limit', 100);
    $user->getChild('bbs');
    $this->assertEquals(count($user->bbs), 5);

    $user = new Users(1);
    $user->setChildConstraint('limit', 100);
    $user->setChildCondition('bbs', array('body' => 'body13'));
    $user->getChild('bbs');
    $this->assertEquals(count($user->bbs), 1);

    $user = new Users(2);
    $user->setChildConstraint('limit', 100);
    $user->setChildCondition('bbs', array('OR_body' => array('body21', 'body23')));
    $user->getChild('bbs');
    $this->assertEquals(count($user->bbs), 2);

    $bbs = new Sabel_DB_Basic('bbs');
    $bbs->save(array('users_id' => 4));

    $user = new Users(4);
    $user->setChildConstraint('limit', 100);
    $user->setChildCondition('bbs', array('OR_title' => array('title41', 'null')));
    $user->getChild('bbs');
    $this->assertEquals(count($user->bbs), 2);
  }

  public function testStatementCheck()
  {
    $tree = new Tree();
    $tree->name('C11');
    $tree->tree_id(4);
    $t = $tree->selectOne();
    $this->assertEquals((int)$t->id, 11);

    $tree->name('null');
    $tree->tree_id(4);
    $t = $tree->selectOne();
    $this->assertEquals((int)$t->id, 8);
  }

  public function testORCondition()
  {
    $ol = new Sabel_DB_Basic('order_line');
    $ol->setConstraint('order', 'id');
    $ol->OR_(array('amount', 'item_id'), array('> 9000', '2'));

    $ols = $ol->select();
    $this->assertEquals((int)$ols[0]->id, 1);
    $this->assertEquals((int)$ols[1]->id, 6);
    $this->assertEquals((int)$ols[2]->id, 9);
    $this->assertEquals((int)$ols[3]->id, 10);

    $this->assertEquals((int)$ols[2]->amount, 10000);
    $this->assertEquals((int)$ols[3]->amount, 50000);
  }

  public function testTransaction()
  {
    $trans1 = new Trans1();
    $data = array();
    $data[] = array('text' => 'trans1');
    $data[] = array('text' => 'trans2');
    $data[] = array('text' => 'trans3');

    $trans1->multipleInsert($data);

    $trans2 = new Trans2();
    $data = array();
    $data[] = array('trans1_id' => 3, 'text' => 'trans21');
    $data[] = array('trans1_id' => 3, 'text' => 'trans22');
    $data[] = array('trans1_id' => 2, 'text' => 'trans23');
    $data[] = array('trans1_id' => 1, 'text' => 'trans24');
    $data[] = array('trans1_id' => 1, 'text' => 'trans25');
    $data[] = array('trans1_id' => 1, 'text' => 'trans26');

    $trans2->multipleInsert($data);

    $trans1 = new Trans1(1);
    $trans1->setChildConstraint('limit', 10);
    $trans1->getChild('trans2');
    $this->assertEquals(count($trans1->trans2) , 3);

    $trans1 = new Trans1(1);
    $trans1->setChildConstraint('limit', 10);
    $trans1->setChildCondition('trans2', array('text' => 'trans24'));
    $trans1->getChild('trans2');
    $this->assertEquals(count($trans1->trans2) , 1);

    $trans1 = new Trans1(3);
    $trans1->setChildConstraint('limit', 10);
    $trans1->setChildCondition('trans2', array('text' => 'trans24'));
    $trans1->getChild('trans2');
    $this->assertEquals(count($trans1->trans2) , 0);

    $trans1 = new Trans1(2);
    $trans1->setChildConstraint('limit', 10);
    $trans1->getChild('trans2');
    $this->assertEquals(count($trans1->trans2) , 1);

    $trans1->execute("DELETE FROM trans1");
    $trans2->execute("DELETE FROM trans2");

    //-------------------------------------------------------------------

    $trans1 = new Trans1(); // connection1
    $trans1->begin();

    $data = array();
    $data[] = array('text' => 'trans1');
    $data[] = array('text' => 'trans2');
    $data[] = array('text' => 'trans3');

    $trans1->multipleInsert($data);

    $trans2 = new Trans2(); // connection2
    $data = array();
    $data[] = array('trans1_id' => 3, 'text' => 'trans21');
    $data[] = array('trans1_id' => 3, 'text' => 'trans22');
    $data[] = array('trans1_id' => 2, 'text' => 'trans23');
    $data[] = array('trans1_id' => 1, 'text' => 'trans24');
    $data[] = array('trans1_id' => 1, 'text' => 'trans25');
    $data[] = array('trans1_id' => 1, 'texx' => 'trans26');  // <- Error && rollback()

    try {
      $trans2->multipleInsert($data);
      $trans2->commit(); // not execute commit()
    } catch (Exception $e) {
    }

    $trans2 = new Trans2();
    $t = $trans2->select();
    $this->assertEquals($t, false); // not found

    $trans1 = new Trans1();
    $t = $trans1->select();
    $this->assertEquals($t, false); // not found
  }

  public function testGetColumnsName()
  {
    $test = new Test();
    $test->save(array('name' => 'a' ,'blood' => 'b', 'test2_id' => 3));
    $colsName = $test->getColumnsName();

    $this->assertEquals($colsName[0], 'id');
    $this->assertEquals($colsName[1], 'name');
    $this->assertEquals($colsName[2], 'blood');
    $this->assertEquals($colsName[3], 'test2_id');

    $test = new Sabel_DB_Basic('seq');
    $test->save(array('text' => 'a'));

    $test = new Test();
    $colsName = $test->getColumnsName('seq');

    $this->assertEquals($colsName[0], 'id');
    $this->assertEquals($colsName[1], 'text');
  }
}

class MysqlHelper
{
  protected $sqls = null;

  protected $tables = array('test', 'test2', 'test3',
                            'customer', 'customer_order', 'order_line',
                            'customer_telephone', 'infinite1', 'infinite2',
                            'seq', 'tree', 'student', 'student_course',
                            'course', 'users', 'status', 'bbs', 'trans1');
  
  public function __construct()
  {
    $dbCon = array();
    $dbCon['dsn']  = 'mysql:host=localhost;dbname=edo';
    $dbCon['user'] = 'root';
    $dbCon['pass'] = '';
    
    Sabel_DB_Connection::addConnection('default', 'pdo', $dbCon);
    /*
    $dbCon = mysql_connect('localhost', 'root', '');
    mysql_select_db('edo', $dbCon);
    Sabel_DB_Connection::addConnection('default', 'mysql', $dbCon);
    */
    
    $dbCon = array();
    $dbCon['dsn']  = 'mysql:host=localhost;dbname=edo2';
    $dbCon['user'] = 'root';
    $dbCon['pass'] = '';
    
    Sabel_DB_Connection::addConnection('default2', 'pdo', $dbCon);
 
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
                id INT2 PRIMARY KEY AUTO_INCREMENT,
                customer_order_id INT2 NOT NULL,
                amount INT4,
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
                 id      INT2 PRIMARY KEY,
                 tree_id INT2,
                 name    VARCHAR(12) )';

    $SQLs[] = 'CREATE TABLE student (
                 id    INT4 PRIMARY KEY AUTO_INCREMENT,
                 name  VARCHAR(24) NOT NULL,
                 birth DATE)';
    
    $SQLs[] = 'CREATE TABLE student_course (
                 student_id INT4 NOT NULL,
                 course_id  INT4 NOT NULL,
                 CONSTRAINT student_course_pkey PRIMARY KEY (student_id, course_id) )';

    $SQLs[] = 'CREATE TABLE course (
                 id   INT4 PRIMARY KEY AUTO_INCREMENT,
                 name VARCHAR(24) )';
                
    $SQLs[] = 'CREATE TABLE users (
                 id        INT4 PRIMARY KEY AUTO_INCREMENT,
                 name      VARCHAR(24) NOT NULL,
                 status_id INT2 )';

    $SQLs[] = 'CREATE TABLE status (
                 id    INT2 PRIMARY KEY AUTO_INCREMENT,
                 state VARCHAR(24) )';

    $SQLs[] = 'CREATE TABLE bbs (
                 id       INT4 PRIMARY KEY AUTO_INCREMENT,
                 users_id INT4 NOT NULL,
                 title    VARCHAR(24),
                 body     VARCHAR(24))';

    $SQLs[] = 'CREATE TABLE trans1 (
                 id    INT4 PRIMARY KEY AUTO_INCREMENT,
                 text  VARCHAR(24)) TYPE=InnoDB';

    $this->sqls = $SQLs;
  }

  public function createTables()
  {
    $obj = new Sabel_DB_Basic();

    foreach ($this->sqls as $sql) {
      $obj->execute($sql);
    }

    $sql  = "CREATE TABLE trans2 (id INT4 PRIMARY KEY AUTO_INCREMENT, trans1_id INT4 NOT NULL,";
    $sql .= "text VARCHAR(24) ) TYPE=InnoDB";

    $trans2 = new Trans2();
    $trans2->execute($sql);
  }

  public function dropTables()
  {
    $obj = new Sabel_DB_Basic();

    try {
      foreach ($this->tables as $table) {
        $obj->execute("DROP TABLE ${table}");
      }
    } catch (Exception $e) {
    }

    $trans2 = new Trans2();
    $trans2->execute("DROP TABLE trans2");
  }
}

class PgsqlHelper
{
  protected $sqls = null;

  protected $tables = array('test', 'test2', 'test3',
                            'customer', 'customer_order', 'order_line',
                            'customer_telephone', 'infinite1', 'infinite2',
                            'seq', 'tree', 'student', 'student_course',
                            'course', 'users', 'bbs', 'status', 'trans1');

  public function __construct()
  {
    $dbCon = pg_connect("host=localhost dbname=edo user=pgsql password=pgsql");
    Sabel_DB_Connection::addConnection('default', 'pgsql', $dbCon);

    $dbCon = array();
    $dbCon['dsn']  = 'pgsql:host=localhost;dbname=edo2';
    $dbCon['user'] = 'pgsql';
    $dbCon['pass'] = 'pgsql';
    Sabel_DB_Connection::addConnection('default2', 'pdo', $dbCon);

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
                amount            INT4,
                item_id           INT2 NOT NULL)';
                
    $SQLs[] = 'CREATE TABLE customer_telephone (
                id          SERIAL PRIMARY KEY,
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
                 id         SERIAL,
                 student_id INT4 NOT NULL,
                 course_id  INT4 NOT NULL,
                 CONSTRAINT student_course_pkey PRIMARY KEY (student_id, course_id) )';

    $SQLs[] = 'CREATE TABLE course (
                 id   SERIAL PRIMARY KEY,
                 name VARCHAR(24) )';
                
    $SQLs[] = 'CREATE TABLE users (
                 id        SERIAL PRIMARY KEY,
                 name      VARCHAR(24) NOT NULL,
                 status_id INT2 )';

    $SQLs[] = 'CREATE TABLE status (
                 id    SERIAL PRIMARY KEY,
                 state VARCHAR(24) )';

    $SQLs[] = 'CREATE TABLE bbs (
                 id       SERIAL PRIMARY KEY,
                 users_id INT4 NOT NULL,
                 title    VARCHAR(24),
                 body     VARCHAR(24))';

    $SQLs[] = 'CREATE TABLE trans1 (
                 id    SERIAL PRIMARY KEY,
                 text  VARCHAR(24) )';

    $this->sqls = $SQLs;
  }
  
  public function createTables()
  {
    $obj = new Sabel_DB_Basic();
    
    foreach ($this->sqls as $sql) {
      @$obj->execute($sql);
    }

    $sql  = "CREATE TABLE trans2 (id SERIAL PRIMARY KEY, trans1_id INT4 NOT NULL, text VARCHAR(24) )";
    try {
      $trans2 = new Trans2();
      $trans2->execute($sql);
    } catch (Exception $e) {
    }
  }

  public function dropTables()
  {
    $obj = new Sabel_DB_Basic();

    foreach ($this->tables as $table) {
      @$obj->execute("DROP TABLE ${table}");
    }

    try {
      $trans2 = new Trans2();
      $trans2->execute("DROP TABLE trans2");
    } catch (Exception $e) {
    }
  }
}

class SQLiteHelper
{
  protected $sqls = null;

  protected $tables = array('test', 'test2', 'test3',
                            'customer', 'customer_order', 'order_line',
                            'customer_telephone', 'infinite1', 'infinite2',
                            'seq', 'tree', 'student', 'student_course',
                            'course', 'users', 'bbs', 'status', 'trans1');

  public function __construct()
  {
    $dbCon = array();
    $dbCon['dsn']  = 'sqlite:Test/data/log1.sq3';

    $dbCon2 = array();
    $dbCon2['dsn']  = 'sqlite:Test/data/log2.sq3';

    Sabel_DB_Connection::addConnection('default', 'pdo', $dbCon);
    Sabel_DB_Connection::addConnection('default2', 'pdo', $dbCon2);

    $SQLs = array();

    $SQLs[] = 'CREATE TABLE test (
                 id       INTEGER PRIMARY KEY,
                 name     VARCHAR(32) NOT NULL,
                 blood    VARCHAR(32),
                 test2_id INT2)';
    
    $SQLs[] = 'CREATE TABLE test2 (
                 id       INTEGER PRIMARY KEY,
                 name     VARCHAR(32) NOT NULL,
                 test3_id INT2)';
                 
    $SQLs[] = 'CREATE TABLE test3 (
                id   INTEGER PRIMARY KEY,
                name VARCHAR(32) NOT NULL)';
                
    $SQLs[] = 'CREATE TABLE customer (
                id   INTEGER PRIMARY KEY,
                name VARCHAR(32) NOT NULL)';
                
    $SQLs[] = 'CREATE TABLE customer_order (
                id          INTEGER PRIMARY KEY,
                customer_id INT2 NOT NULL)';
    
    $SQLs[] = 'CREATE TABLE order_line (
                id                INTEGER PRIMARY KEY,
                customer_order_id INT2 NOT NULL,
                amount            INT4,
                item_id           INT2 NOT NULL)';
                
    $SQLs[] = 'CREATE TABLE customer_telephone (
                id          INTEGER PRIMARY KEY,
                customer_id INT2 NOT NULL,
                telephone   VARCHAR(32))';
                
    $SQLs[] = 'CREATE TABLE infinite1 (
                id           INTEGER PRIMARY KEY,
                infinite2_id INT2 NOT NULL)';
                
    $SQLs[] = 'CREATE TABLE infinite2 (
                id           INTEGER PRIMARY KEY,
                infinite1_id int2 NOT NULL)';
                
    $SQLs[] = 'CREATE TABLE seq (
                 id   INTEGER PRIMARY KEY,
                 text VARCHAR(65536) NOT NULL)';
    
    $SQLs[] = 'CREATE TABLE tree (
                 id      INTEGER PRIMARY KEY,
                 tree_id INT2,
                 name    VARCHAR(12) )';
                
    $SQLs[] = 'CREATE TABLE student (
                 id    INTEGER PRIMARY KEY,
                 name  VARCHAR(24) NOT NULL,
                 birth DATE)';
    
    $SQLs[] = 'CREATE TABLE student_course (
                 student_id INT4 NOT NULL,
                 course_id  INT4 NOT NULL,
                 CONSTRAINT student_course_pkey PRIMARY KEY (student_id, course_id) )';

    $SQLs[] = 'CREATE TABLE course (
                 id   INTEGER PRIMARY KEY,
                 name VARCHAR(24) )';
                
    $SQLs[] = 'CREATE TABLE users (
                 id        INTEGER PRIMARY KEY,
                 name      VARCHAR(24) NOT NULL,
                 status_id INT2 )';

    $SQLs[] = 'CREATE TABLE status (
                 id    INTEGER PRIMARY KEY,
                 state VARCHAR(24) )';

    $SQLs[] = 'CREATE TABLE bbs (
                 id       INTEGER PRIMARY KEY,
                 users_id INT4 NOT NULL,
                 title    VARCHAR(24),
                 body     VARCHAR(24))';

    $SQLs[] = 'CREATE TABLE trans1 (
                 id    INTEGER PRIMARY KEY,
                 text  VARCHAR(24) )';

    $this->sqls = $SQLs;
  }
  
  public function createTables()
  {
    try {
    $obj = new Sabel_DB_Basic();
    
    foreach ($this->sqls as $sql) {
      $obj->execute($sql);
    }

    $sql  = "CREATE TABLE trans2 (id INTEGER PRIMARY KEY, trans1_id INT4 NOT NULL, text VARCHAR(24) )";
    $trans2 = new Trans2();
    $trans2->execute($sql);
    } catch (Exception $e) {
      print_r($e);
    }
  }

  public function dropTables()
  {
    $obj = new Sabel_DB_Basic();

    foreach ($this->tables as $table) {
      $obj->execute("DROP TABLE ${table}");
    }

    $trans2 = new Trans2();
    $trans2->execute("DROP TABLE trans2");
  }
}
//-----------------------------------------------------------------

abstract class Mapper_Default extends Sabel_DB_Mapper
{
  public function __construct($param1 = null, $param2 = null)
  {
    $this->setDriver('default');
    parent::__construct($param1, $param2);
  }
}

class Trans1 extends Sabel_DB_Mapper
{
  public function __construct($param1 = null, $param2 = null)
  {
    $this->setDriver('default');
    parent::__construct($param1, $param2);
  }
}

class Trans2 extends Sabel_DB_Mapper
{
  public function __construct($param1 = null, $param2 = null)
  {
    $this->setDriver('default2');
    parent::__construct($param1, $param2);
  }
}

class Test extends Mapper_Default
{
  protected $withParent = true;

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

class Customer extends Mapper_Default
{
  protected $myChildren = array('customer_order', 'customer_telephone');
  protected $defChildConstraints = array('limit' => 10);

  public function __construct($param1 = null, $param2 = null)
  {
    parent::__construct($param1, $param2);
    $this->setChildConstraint('customer_order', array('limit' => 10));
  }
}

class Customer_Order extends Mapper_Default
{
  protected $myChildren = 'order_line';

  public function __construct($param1 = null, $param2 = null)
  {
    parent::__construct($param1, $param2);
    $this->setChildConstraint('order_line', array('limit' => 10));
  }
}

class Tree extends Sabel_DB_Tree
{

}

class Student extends Sabel_DB_Bridge
{

}

class Course extends Sabel_DB_Bridge
{

}

class Users extends Mapper_Default
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
