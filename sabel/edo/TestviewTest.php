<?php

if (!defined("PHPUnit2_MAIN_METHOD")) {
    define("PHPUnit2_MAIN_METHOD", "TestviewTest::main");
}

require_once "PHPUnit2/Framework/TestCase.php";
require_once "PHPUnit2/Framework/TestSuite.php";
require_once "PHPUnit2/Framework/IncompleteTestError.php";

//require_once "../Functions.php";
require_once "RecordObject.php";
require_once "DBConnection.php";

require_once "driver/Interface.php";
require_once "driver/Pdo.php";
require_once "driver/Pgsql.php";

class TestviewTest extends PHPUnit2_Framework_TestCase {

  public static function main() {
    require_once "PHPUnit2/TextUI/TestRunner.php";

    $suite  = new PHPUnit2_Framework_TestSuite("TestviewTest");
    $result = PHPUnit2_TextUI_TestRunner::run($suite);
  }

  protected function setUp() {

    // pdo postgres
    //$dbCon = array();
    //$dbCon['dsn']  = 'pgsql:host=127.0.0.1;dbname=test';
    //$dbCon['user'] = 'pgsql';
    //$dbCon['pass'] = 'pgsql';
    //Sabel_Edo_DBConnection::addConnection('user', 'pdo', $dbCon);

    // pdo mysql
    $dbCon = array();
    $dbCon['dsn']  = 'mysql:host=localhost;dbname=test';
    $dbCon['user'] = 'develop';
    $dbCon['pass'] = 'develop';
    Sabel_Edo_DBConnection::addConnection('user', 'pdo', $dbCon);

    // native postgres
    //$dbCon = pg_connect('host=127.0.0.1 dbname=test user=pgsql password=pgsql');
    //Sabel_Edo_DBConnection::addConnection('user', 'pgsql', $dbCon);

    //native mysql
    //$dbCon = new Mysqli('localhost', 'develop', 'develop', 'test');
    //Sabel_Edo_DBConnection::addConnection('user', 'mysqli', $dbCon);

    /*
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

    $sql  = "CREATE TABLE customer (id int2 NOT NULL,name varchar NOT NULL,";
    $sql .= " CONSTRAINT customer_pkey PRIMARY KEY (id) );";
    $obj->execute($sql);

    $this->customer = new Customer();

    $sql  = "CREATE TABLE customer_order (id int2 NOT NULL,customer_id int2 NOT NULL,";
    $sql .= " CONSTRAINT customer_order_pkey PRIMARY KEY (id) );";
    $obj->execute($sql);

    $this->order = new Common_Record('customer_order');

    $sql  = "CREATE TABLE order_line (id int2 NOT NULL,customer_order_id int2 NOT NULL,amount int4 NOT NULL, item_id int2 NOT NULL,";
    $sql .= " CONSTRAINT order_line_pkey PRIMARY KEY (id) );";
    $obj->execute($sql);

    $this->orderLine = new Common_Record('order_line');

    $sql  = "CREATE TABLE customer_telephone (id int2 NOT NULL,customer_id int2 NOT NULL,telephone varchar,";
    $sql .= " CONSTRAINT customer_telephon_pkey PRIMARY KEY (id) );";
    $obj->execute($sql);

    $this->telephone = new Common_Record('customer_telephone');
    */

    $this->test      = new Test();
    $this->test2     = new Common_Record('test2');
    $this->test3     = new Common_Record('test3');
    $this->customer  = new Customer();
    $this->order     = new Common_Record('customer_order');
    $this->orderLine = new Common_Record('order_line');
    $this->telephone = new Common_Record('customer_telephone');
  }

  protected function tearDown() {

  }

  public function testConstraint()
  {
    $insertData = array();
    $insertData[] = array('id' => 1, 'name' => 'tanaka');
    $insertData[] = array('id' => 2, 'name' => 'ueda');

    foreach ($insertData as $data) {
      $this->customer->multipleInsert($data);
    }
    $this->assertEquals($this->customer->getCount(), 2);

    $insertData = array();
    $insertData[] = array('id' => 1, 'customer_id' => 1);
    $insertData[] = array('id' => 2, 'customer_id' => 1);
    $insertData[] = array('id' => 3, 'customer_id' => 2);
    $insertData[] = array('id' => 4, 'customer_id' => 2);
    $insertData[] = array('id' => 5, 'customer_id' => 1);
    $insertData[] = array('id' => 6, 'customer_id' => 1);

    foreach ($insertData as $data) {
      $this->order->multipleInsert($data);
    }

    $o = new Common_Record('customer_order');
    $o->setSelectType(Sabel_Edo_RecordObject::SE);

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

    $insertData = array();
    $insertData[] = array('id' => 1,  'customer_id' => 1, 'telephone' => '09011111111');
    $insertData[] = array('id' => 2,  'customer_id' => 2, 'telephone' => '09022221111');
    $insertData[] = array('id' => 3,  'customer_id' => 1, 'telephone' => '09011112222');
    $insertData[] = array('id' => 4,  'customer_id' => 2, 'telephone' => '09022222222');

    foreach ($insertData as $data) {
      $this->telephone->multipleInsert($data);
    }
    $this->assertEquals($this->orderLine->getCount(), 11);
  }

  public function testInsert()
  {
    $test2 = new Common_Record('test2');
    $test2->id = 1;
    $test2->name = 'test21';
    $test2->test3_id = '2';
    $test2->save();

    $test2 = new Common_Record('test2');
    $test2->id = 2;
    $test2->name = 'test22';
    $test2->test3_id = '1';
    $test2->save();

    $test2 = new Common_Record('test2');
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
    $test->setProjection(array('id','blood'));

    $obj2 = $test->selectOne(2);
    $this->assertEquals((int)$obj2->id, 2);
    $this->assertNotEquals($obj2->name, 'yo_shida');
    $this->assertEquals($obj2->blood, 'B');
    $this->assertNotEquals((int)$obj2->test2_id, 2);
  }

  public function testSelect()
  {
    /*
    $c = new Customer();
    for ($i = 0; $i < 100; $i++) {
      $c->select();
    }
    */
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

    $this->test->OR_id(3, 4);
    $obj = $this->test->select();
    $this->assertEquals($obj[0]->name, 'uchida');
    $this->assertEquals($obj[1]->name, 'ueda');
    $this->assertEquals($obj[2]->name, null);
  }

  public function testInfiniteLoop()
  {
    /*
    $obj  = new Common_Record();
    $sql  = "CREATE TABLE infinite1 (id int2 NOT NULL,infinite2_id int2 NOT NULL,";
    $sql .= " CONSTRAINT infinite1_pkey PRIMARY KEY (id) );";
    $obj->execute($sql);

    $sql  = "CREATE TABLE infinite2 (id int2 NOT NULL,infinite1_id int2 NOT NULL,";
    $sql .= " CONSTRAINT infinite2_pkey PRIMARY KEY (id) );";
    $obj->execute($sql);
    */

    $in1 = new Common_Record('infinite1');
    $in1->id           = 1;
    $in1->infinite2_id = 2;
    $in1->save();

    $in2 = new Common_Record('infinite2');
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

    $child = $obj->test2;
    $this->assertEquals((int)$child->id, 1);
    $this->assertEquals($child->name, 'test21');
    $this->assertEquals((int)$child->test3_id, 2);

    $child2 = $child->test3;
    $this->assertEquals((int)$child2->id, 2);
    $this->assertEquals($child2->name, 'test32');
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

    //$this->assertEquals((int)$objs[0]->customer_order[4]->order_line[0]->id, 2);
    //$this->assertEquals((int)$objs[0]->customer_order[4]->order_line[1]->id, 8);
    //$this->assertEquals((int)$objs[0]->customer_order[4]->order_line[2]->id, 11);

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

    $objs[0]->killAll('customer_telephone');

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
    $seq = new Common_Record('seq');

    $seq->text = 'test';
    $id = $seq->save();
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
}

if (PHPUnit2_MAIN_METHOD == "TestviewTest::main") {
    TestviewTest::main();
}
?>
