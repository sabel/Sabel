<?php

class Test_DB_Test extends SabelTestCase
{
  private static $count  = 0;
  private static $dbList = array('mysql', 'pgsql', 'sqlite');

  public static $connectName = '';

  public static $TABLES = array('test', 'test2', 'test3',
                                'customer', 'customer_order', 'order_line',
                                'customer_telephone', 'infinite1', 'infinite2',
                                'seq', 'tree', 'student', 'student_course',
                                'course', 'users', 'status', 'bbs', 'trans1');


  public function testConstraint()
  {
    $customer = new Customer();

    //$dbname = Sabel_DB_Connection::getDB($customer->getConnectName());
    //$this->assertEquals($dbname, self::$dbList[self::$count++]);

    $insertData   = array();
    $insertData[] = array('id' => 1, 'name' => 'tanaka');
    $insertData[] = array('id' => 2, 'name' => 'ueda');
    $customer->multipleInsert($insertData);

    $this->assertEquals($customer->getCount(), 2);

    $order = new CustomerOrder();

    $insertData   = array();
    $insertData[] = array('id' => 1, 'customer_id' => 1);
    $insertData[] = array('id' => 2, 'customer_id' => 1);
    $insertData[] = array('id' => 3, 'customer_id' => 2);
    $insertData[] = array('id' => 4, 'customer_id' => 2);
    $insertData[] = array('id' => 5, 'customer_id' => 1);
    $insertData[] = array('id' => 6, 'customer_id' => 1);
    $order->multipleInsert($insertData);

    $o = new CustomerOrder();
    $o->enableParent();
    $res = $o->select();
    $this->assertEquals((int)$res[0]->Customer->id, 1);
    $this->assertEquals((int)$res[2]->Customer->id, 2);

    $this->assertEquals($order->getCount(), 6);

    $cu  = new Customer();
    $cus = $cu->select();
    $this->assertEquals((int)$cus[0]->CustomerOrder[0]->id, 1);
    $this->assertEquals((int)$cus[0]->CustomerOrder[1]->id, 2);
    $this->assertEquals((int)$cus[1]->CustomerOrder[0]->id, 3);
    $this->assertEquals((int)$cus[1]->CustomerOrder[1]->id, 4);
    $this->assertEquals((int)$cus[0]->CustomerOrder[2]->id, 5);
    $this->assertEquals((int)$cus[0]->CustomerOrder[3]->id, 6);

    $cu = new Customer();
    $cu->setChildConstraint('CustomerOrder', array('order' => 'id desc'));
    $cus = $cu->select();
    $this->assertEquals((int)$cus[0]->CustomerOrder[0]->id, 6);
    $this->assertEquals((int)$cus[0]->CustomerOrder[1]->id, 5);
    $this->assertEquals((int)$cus[1]->CustomerOrder[0]->id, 4);
    $this->assertEquals((int)$cus[1]->CustomerOrder[1]->id, 3);
    $this->assertEquals((int)$cus[0]->CustomerOrder[2]->id, 2);
    $this->assertEquals((int)$cus[0]->CustomerOrder[3]->id, 1);

    $cu  = new Customer();
    $cu->setChildConstraint('CustomerOrder', array('offset' => 1));
    $cus = $cu->select();
    $this->assertEquals((int)$cus[0]->CustomerOrder[0]->id, 2);
    $this->assertEquals((int)$cus[1]->CustomerOrder[0]->id, 4);
    $this->assertEquals((int)$cus[0]->CustomerOrder[1]->id, 5);
    $this->assertEquals((int)$cus[0]->CustomerOrder[2]->id, 6);
    
    $cu  = new Customer();
    $cu->setChildConstraint('CustomerOrder', array('limit' => 2));
    $cus = $cu->select();
    $this->assertEquals((int)$cus[0]->CustomerOrder[0]->id, 1);
    $this->assertEquals((int)$cus[0]->CustomerOrder[1]->id, 2);
    $this->assertEquals((int)$cus[1]->CustomerOrder[0]->id, 3);
    $this->assertEquals((int)$cus[1]->CustomerOrder[1]->id, 4);
    @$this->assertNull($cus[0]->CustomerOrder[2]->id);
    @$this->assertNull($cus[0]->CustomerOrder[3]->id);
  }

  public function testInsert()
  {
    $test2 = new Test2();
    $test2->id   = 1;
    $test2->name = 'test21';
    $test2->test3_id = '2';
    $test2->save();

    $test2 = new Test2();
    $test2->id   = 2;
    $test2->name = 'test22';
    $test2->test3_id = '1';
    $test2->save();
    
    $test2 = new Test2();
    $test2->id   = 3;
    $test2->name = 'test23';
    $test2->test3_id = '2';
    $test2->save();

    $test2 = new Test2();
    $obj   = $test2->selectOne(3);
    $this->assertEquals($obj->name, 'test23');
    
    $test3 = new Test3();
    $test3->id = 1;
    $test3->name = 'test31';
    $test3->save();
    
    $test3->id = 2;
    $test3->name = 'test32';
    $test3->save();
    
    $test3->name('test31');
    $obj = $test3->selectOne();
    $this->assertEquals((int)$obj->id, 1);
  }

  public function testMultipleInsert()
  {
    $test = new Test();

    $insertData = array();
    $insertData[] = array('id' => 1, 'name' => 'tanaka',   'blood' => 'A',  'test2_id' => 1);
    $insertData[] = array('id' => 2, 'name' => 'yo_shida', 'blood' => 'B',  'test2_id' => 2);
    $insertData[] = array('id' => 3, 'name' => 'uchida',   'blood' => 'AB', 'test2_id' => 1);
    $insertData[] = array('id' => 4, 'name' => 'ueda',     'blood' => 'A',  'test2_id' => 3);
    $insertData[] = array('id' => 5, 'name' => 'seki',     'blood' => 'O',  'test2_id' => 3);
    $insertData[] = array('id' => 6, 'name' => 'uchida',   'blood' => 'A',  'test2_id' => 1);
    $test->multipleInsert($insertData);

    $ro = $test->select();
    $this->assertEquals(count($ro), 6);

    $test->enableParent();
    $obj = $test->selectOne(5);
    $this->assertEquals($obj->name, 'seki');
    $this->assertEquals((int)$obj->Test2->id, 3);
    
    $obj = $test->selectOne('name', 'seki');
    $this->assertEquals((int)$obj->id, 5);

    $orderLine = new OrderLine();

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
    $orderLine->multipleInsert($insertData);
    $this->assertEquals($orderLine->getCount(), 11);
    
    $telephone = new CustomerTelephone();

    $insertData   = array();
    $insertData[] = array('id' => 1,  'customer_id' => 1, 'telephone' => '09011111111');
    $insertData[] = array('id' => 2,  'customer_id' => 2, 'telephone' => '09022221111');
    $insertData[] = array('id' => 3,  'customer_id' => 1, 'telephone' => '09011112222');
    $insertData[] = array('id' => 4,  'customer_id' => 2, 'telephone' => '09022222222');
    $telephone->multipleInsert($insertData);

    $this->assertEquals($orderLine->getCount(), 11);
    
    $tree = new Tree();
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

    $student = new Student();
    $insertData   = array();
    $insertData[] = array('id' => 1, 'name' => 'tom',   'birth' => '1983/08/17');
    $insertData[] = array('id' => 2, 'name' => 'john',  'birth' => '1983/08/18');
    $insertData[] = array('id' => 3, 'name' => 'bob',   'birth' => '1983/08/19');
    $insertData[] = array('id' => 4, 'name' => 'marcy', 'birth' => '1983/08/20');
    $insertData[] = array('id' => 5, 'name' => 'ameri', 'birth' => '1983/08/21');
    $student->multipleInsert($insertData);

    $course = new Course();
    $insertData   = array();
    $insertData[] = array('id' => 1, 'name' => 'Mathematics');
    $insertData[] = array('id' => 2, 'name' => 'Physics');
    $insertData[] = array('id' => 3, 'name' => 'Science');
    $insertData[] = array('id' => 4, 'name' => 'Economic');
    $insertData[] = array('id' => 5, 'name' => 'Psychology');
    $course->multipleInsert($insertData);

    $sc = new StudentCourse();
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
    $insertData[] = array('id' => 1, 'name' => 'Tarou'  , 'status_id' => 1);
    $insertData[] = array('id' => 2, 'name' => 'Hanako' , 'status_id' => 2);
    $insertData[] = array('id' => 3, 'name' => 'Maruo'  , 'status_id' => 1);
    $insertData[] = array('id' => 4, 'name' => 'Atsuko' , 'status_id' => 1);
    $users->multipleInsert($insertData);

    $s = new Status();
    $s->state = 'normal';
    $s->save();
    $s->state = 'invalid';
    $s->save();

    $s  = new Status();
    $ss = $s->select();

    $this->assertEquals($ss[0]->state, 'normal');
    $this->assertEquals($ss[1]->state, 'invalid');

    $bbs = new Bbs();

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

  public function testUpdateOrInsert()
  {
    $test = new Test(7); // not found 
    
    @$this->assertEquals($test->name, null);
    @$this->assertEquals($test->blood, null);
    
    if ($test->isSelected()) {
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
    
    if ($test->isSelected()) {
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
    $test = new Test();

    $obj = $test->selectOne(7);
    $this->assertEquals($obj->blood, 'AB');
    
    $test->remove(7);
    
    $obj = $test->selectOne(7);
    $this->assertNotEquals($obj->blood, 'AB');
    $this->assertEquals($obj->blood, null);

    $t = new Test(99);
    $this->assertEquals($t->isSelected(), false);
    $t->name     = 'test99';
    $t->blood    = 'C';
    $t->test2_id = '3';
    $t->save();

    $t = new Test(99);
    $this->assertEquals($t->isSelected(), true);
    $t->remove();
  }

  public function testProjection()
  {
    $test = new Test();
    $obj = $test->selectOne(2);
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

  public function testSelectDefaultResult()
  {
    $test = new Test();
    $obj  = $test->selectOne(1);

    $obj2 = new Test(1);
    
    $this->assertEquals((int)$obj->id, (int)$obj2->id);
    $this->assertEquals($obj->name, $obj2->name);
    $this->assertEquals($obj->blood, $obj2->blood);
    $this->assertEquals((int)$obj->test2_id, (int)$obj->test2_id);
    
    //----------------------------------------------
    
    $test = new Test();
    $test->LIKE_name(array('%da%', false));
    $obj = $test->select();
    $this->assertEquals(count($obj), 4); // yo_shida, uchida, ueda, uchida
    
    $test = new Test();
    $test->LIKE_name('yo_shida');
    $obj = $test->select();
    $this->assertEquals(count($obj), 1);
    $this->assertEquals($obj[0]->name, 'yo_shida'); // yo_shida

    $test = new Test();
    $test->LIKE_name(array('%i_a', false));
    $obj = $test->select();
    $this->assertEquals(count($obj), 3); // yo_shida, uchida, uchida

    $test = new Test();
    $test->OR_id(array('3', '4'));
    $obj = $test->select();

    $this->assertEquals($obj[0]->name, 'uchida');
    $this->assertEquals($obj[1]->name, 'ueda');
    @$this->assertNull($obj[2]->name);

    $test = new Test();
    $test->OR_id(array('< 2', '> 5'));
    $obj = $test->select();
    $this->assertEquals((int) $obj[0]->id, 1);
    $this->assertEquals((int) $obj[1]->id, 6);

    $test = new Test();
    $test->OR_id(array('<= 2', '>= 5'));
    $test->sconst('order', 'id');
    $obj = $test->select();
    $this->assertEquals((int) $obj[0]->id, 1);
    $this->assertEquals((int) $obj[1]->id, 2);
    $this->assertEquals((int) $obj[2]->id, 5);
    $this->assertEquals((int) $obj[3]->id, 6);
  }

  public function testInfiniteLoop()
  {
    $in1 = new Infinite1();
    $in1->id           = 1;
    $in1->infinite2_id = 2;
    $in1->save();
    
    $in2 = new Infinite2();
    $in2->id           = 2;
    $in2->infinite1_id = 1;
    $in2->save();
    
    $in1->enableParent();
    $objs = $in1->select();
    $obj = $objs[0];

    $data = $obj->Infinite2->toArray();
    $this->assertFalse(in_array('Infinite1', array_keys($data)));
    
    $this->assertEquals((int)$obj->infinite2_id, (int)$obj->Infinite2->id);
    $this->assertEquals((int)$obj->Infinite2->infinite1_id, 1);
    $this->assertEquals($obj->Infinite2->Infinite1, null);
  }

  public function testSelectParentObject()
  {
    $obj = new Test(1);
    
    $this->assertEquals((int)$obj->id, 1);
    $this->assertEquals($obj->name, 'tanaka');
    $this->assertEquals($obj->blood, 'A');
    $this->assertEquals((int)$obj->test2_id, 1);
    
    $parent = $obj->Test2;
    $this->assertEquals((int)$parent->id, 1);
    $this->assertEquals($parent->name, 'test21');
    $this->assertEquals((int)$parent->test3_id, 2);
    
    $parent2 = $parent->Test3;
    $this->assertEquals((int)$parent2->id, 2);
    $this->assertEquals($parent2->name, 'test32');
  }
  
  public function testGetChild()
  {
    $customer = new Customer();

    $customer->setChildConstraint('limit', 10);
    $cu = $customer->selectOne(1);
    $this->assertEquals($cu->name, 'tanaka');
    
    $orders = $cu->CustomerOrder;
    $this->assertEquals(count($orders), 4);
    
    $this->assertEquals((int)$orders[0]->id, 1);
    $this->assertEquals((int)$orders[1]->id, 2);
    $this->assertEquals((int)$orders[2]->id, 5);
    $this->assertEquals((int)$orders[3]->id, 6);
    
    //------------------------------------------------------
    
    $cu->setChildConstraint('CustomerOrder',
                            array('limit' => 10, 'order' => 'id desc'));
                            
    $cu->getChild('CustomerOrder');
    
    $orders = $cu->CustomerOrder;
    $this->assertEquals(count($orders), 4);
    
    $this->assertEquals((int)$orders[0]->id, 6);
    $this->assertEquals((int)$orders[1]->id, 5);
    $this->assertEquals((int)$orders[2]->id, 2);
    $this->assertEquals((int)$orders[3]->id, 1);
    
    //------------------------------------------------------
    
    $cu->setChildConstraint('CustomerOrder',
                            array('limit'  => 2, 'offset' => 2, 'order'  => 'id desc'));
                            
    $cu->getChild('CustomerOrder');
    
    $orders = $cu->CustomerOrder;
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
    $ch = $c->newChild('CustomerOrder');
    
    $ch->id = 7;
    $ch->save();  // auto insert parent_id
    
    $co = new CustomerOrder();
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
    $this->assertNotEquals($objs[0]->CustomerOrder, null);
    $this->assertNotEquals($objs[1]->CustomerOrder, null);
    
    $this->assertEquals(count($objs[0]->CustomerOrder), 5);
    $this->assertEquals(count($objs[1]->CustomerOrder), 2);
    
    $this->assertEquals((int)$objs[0]->CustomerOrder[0]->id, 1);
    $this->assertEquals((int)$objs[0]->CustomerOrder[1]->id, 2);
    $this->assertEquals((int)$objs[1]->CustomerOrder[0]->id, 3);
    $this->assertEquals((int)$objs[1]->CustomerOrder[1]->id, 4);
    $this->assertEquals((int)$objs[0]->CustomerOrder[2]->id, 5);
    $this->assertEquals((int)$objs[0]->CustomerOrder[3]->id, 6);
    $this->assertEquals((int)$objs[0]->CustomerOrder[4]->id, 7);
    
    //-------------------------------------------------------
    
    $cu   = new Customer();
    $cu->setChildConstraint(array('limit' => 10,
                                  'order' => 'id desc')); // default: for telephone & order_line

    $cu->setChildConstraint('CustomerOrder', array('order' => 'id desc'));
    $objs = $cu->select();
    
    $this->assertEquals((int)$objs[0]->CustomerOrder[0]->id, 7);
    $this->assertEquals((int)$objs[0]->CustomerOrder[1]->id, 6);
    $this->assertEquals((int)$objs[0]->CustomerOrder[2]->id, 5);
    $this->assertEquals((int)$objs[1]->CustomerOrder[0]->id, 4);
    $this->assertEquals((int)$objs[1]->CustomerOrder[1]->id, 3);
    $this->assertEquals((int)$objs[0]->CustomerOrder[3]->id, 2);
    $this->assertEquals((int)$objs[0]->CustomerOrder[4]->id, 1);
    
    $this->assertEquals((int)$objs[0]->CustomerOrder[4]->OrderLine[0]->id, 11);
    $this->assertEquals((int)$objs[0]->CustomerOrder[4]->OrderLine[1]->id, 8);
    $this->assertEquals((int)$objs[0]->CustomerOrder[4]->OrderLine[2]->id, 2);
    
    //-------------------------------------------------------
    
    $cu   = new Customer();
    $cu->setChildConstraint('limit', 10);
    $objs = $cu->select();
    $this->assertNotEquals((int)$objs[0]->CustomerOrder[0]->order_line, null);
    $this->assertNotEquals((int)$objs[1]->CustomerOrder[0]->order_line, null);
    
    $this->assertEquals((int)$objs[0]->CustomerOrder[0]->OrderLine[0]->id, 2);
    $this->assertEquals((int)$objs[0]->CustomerOrder[0]->OrderLine[1]->id, 8);
    $this->assertEquals((int)$objs[0]->CustomerOrder[0]->OrderLine[2]->id, 11);
    @$this->assertNull($objs[0]->CustomerOrder[0]->OrderLine[3]->id);  // hasn't
    
    $this->assertEquals((int)$objs[0]->CustomerOrder[1]->OrderLine[0]->id, 3);
    $this->assertEquals((int)$objs[0]->CustomerOrder[1]->OrderLine[0]->item_id, 3);
    $this->assertEquals((int)$objs[0]->CustomerOrder[1]->OrderLine[1]->id, 4);
    $this->assertEquals((int)$objs[0]->CustomerOrder[1]->OrderLine[1]->item_id, 1);
    @$this->assertNull($objs[0]->CustomerOrder[1]->OrderLine[2]->id);  // hasn't
    
    $this->assertEquals((int)$objs[0]->CustomerOrder[2]->OrderLine[0]->id, 1);
    $this->assertEquals((int)$objs[0]->CustomerOrder[2]->OrderLine[0]->item_id, 2);
    $this->assertEquals((int)$objs[0]->CustomerOrder[2]->OrderLine[1]->id, 7);
    $this->assertEquals((int)$objs[0]->CustomerOrder[2]->OrderLine[1]->item_id, 3);
    @$this->assertNull($objs[0]->CustomerOrder[2]->OrderLine[2]->id);  // hasn't
    
    $this->assertEquals((int)$objs[1]->CustomerOrder[0]->OrderLine[0]->id, 6);
    $this->assertEquals((int)$objs[1]->CustomerOrder[0]->OrderLine[0]->item_id, 2);
    @$this->assertNull($objs[1]->CustomerOrder[0]->OrderLine[1]->id);  // hasn't
    $this->assertEquals((int)$objs[1]->CustomerOrder[1]->OrderLine[0]->id, 5);
    $this->assertEquals((int)$objs[1]->CustomerOrder[1]->OrderLine[0]->item_id, 3);
    @$this->assertNull($objs[1]->CustomerOrder[1]->OrderLine[1]->id);  // hasn't
    
    $this->assertEquals((int)$objs[0]->CustomerTelephone[0]->id, 1);
    $this->assertEquals((int)$objs[0]->CustomerTelephone[1]->id, 3);
    $this->assertEquals((int)$objs[1]->CustomerTelephone[0]->id, 2);
    $this->assertEquals((int)$objs[1]->CustomerTelephone[1]->id, 4);
    
    $this->assertEquals($objs[0]->CustomerTelephone[0]->telephone, '09011111111');
    $this->assertEquals($objs[0]->CustomerTelephone[1]->telephone, '09011112222');
    $this->assertEquals($objs[1]->CustomerTelephone[0]->telephone, '09022221111');
    $this->assertEquals($objs[1]->CustomerTelephone[1]->telephone, '09022222222');
    
    //--------------------------------------------------------------------
    
    $objs[0]->clearChild('CustomerTelephone');
    
    $cu   = new Customer();
    $cu->setChildConstraint('limit', 10);
    $objs = $cu->select();

    @$this->assertNull($objs[0]->CustomerTelephone[0]->telephone);
    @$this->assertNull($objs[0]->CustomerTelephone[1]->telephone);
    $this->assertEquals($objs[1]->CustomerTelephone[0]->telephone, '09022221111');
    $this->assertEquals($objs[1]->CustomerTelephone[1]->telephone, '09022222222');
  }

  public function testGetCount()
  {
    $test = new Test();
    // all count ---------------------------------
    $count = $test->getCount();
    $this->assertEquals($count, 6);
    
    //--------------------------------------------
    $count = $test->getCount('< 5');
    $this->assertEquals($count, 4);
    
    $count = $test->getCount('id', '< 4');
    $this->assertEquals($count, 3);
    
    $test->id('< 3');
    $count = $test->getCount();
    $this->assertEquals($count, 2);
  }
  
  public function testAggregate()
  {
    $order = new CustomerOrder();

    $func  = 'sum(amount) AS sum_amount,';
    $func .= 'avg(amount) AS avg_amount, count(amount) AS count_amount';

    $order->setConstraint('order', 'sum_amount desc');
    $result = $order->aggregate($func, 'order_line');
    
    $this->assertEquals((int)$result[0]->sum_amount, 60000);
    $this->assertEquals((int)$result[1]->sum_amount, 13000);
    $this->assertEquals((int)$result[2]->sum_amount, 9000);
    $this->assertEquals((int)$result[3]->sum_amount, 6500);
    $this->assertEquals((int)$result[4]->sum_amount, 3500);
    $this->assertEquals((int)$result[5]->sum_amount, 1500);
    
    $this->assertEquals((int)$result[0]->count_amount, 2);
    $this->assertEquals((int)$result[0]->avg_amount,   30000);

    $line = new OrderLine();
    $line->setConstraint('order', 'sum_amount desc');
    $result = $line->aggregate($func, null, 'customer_order_id');

    $this->assertEquals((int)$result[0]->sum_amount, 60000);
    $this->assertEquals((int)$result[1]->sum_amount, 13000);
    $this->assertEquals((int)$result[2]->sum_amount, 9000);
    $this->assertEquals((int)$result[3]->sum_amount, 6500);
    $this->assertEquals((int)$result[4]->sum_amount, 3500);
    $this->assertEquals((int)$result[5]->sum_amount, 1500);
    
    $this->assertEquals((int)$result[0]->count_amount, 2);
    $this->assertEquals((int)$result[0]->avg_amount,   30000);
  }

  public function testAllUpdate()
  {
    $line = new OrderLine();
    $line->setCondition('customer_order_id', 1);
    $line->allUpdate(array('amount' => 1000000));

    $line  = new OrderLine();
    $line->sconst('order', 'id');
    $lines = $line->select();

    $this->assertEquals((int)$lines[0]->amount, 1000);
    $this->assertEquals((int)$lines[1]->amount, 1000000);
    $this->assertEquals((int)$lines[2]->amount, 5000);
    $this->assertEquals((int)$lines[3]->amount, 8000);
    $this->assertEquals((int)$lines[4]->amount, 9000);
    $this->assertEquals((int)$lines[5]->amount, 1500);
    $this->assertEquals((int)$lines[6]->amount, 2500);
    $this->assertEquals((int)$lines[7]->amount, 1000000);
    $this->assertEquals((int)$lines[8]->amount, 10000);
    $this->assertEquals((int)$lines[9]->amount, 50000);
    $this->assertEquals((int)$lines[10]->amount, 1000000);

    $line = new OrderLine(2);
    $line->save(array('amount' => 3000));

    $line = new OrderLine(8);
    $line->save(array('amount' => 3000));

    $line = new OrderLine(11);
    $line->save(array('amount' => 500));
  }

  public function testSeq()
  {
    $seq = new Seq();

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
    
    $this->assertEquals((int)$t->Tree->id, 1);
    $this->assertEquals((int)$t->Tree->tree_id, 0);
    $this->assertEquals($t->Tree->name, 'A');

    $t = $tree->selectOne(5);
    $this->assertEquals((int)$t->id, 5);
    $this->assertEquals((int)$t->tree_id, 1);
    $this->assertEquals($t->name, 'A5');

    $this->assertEquals((int)$t->Tree->id, 1);
    $this->assertEquals((int)$t->Tree->tree_id, 0);
    $this->assertEquals($t->Tree->name, 'A');

    $t = new Tree();
    $root = $t->getRoot();
    $this->assertEquals((int)$root[0]->id, 1);
    $this->assertEquals((int)$root[1]->id, 2);
    $this->assertEquals((int)$root[2]->id, 4);
    @$this->assertNull($root[3]);

    $root[0]->setChildConstraint(array('limit' => 10));
    $root[0]->getChild('Tree');
    $this->assertEquals((int)$root[0]->Tree[0]->id, 3);
    $this->assertEquals((int)$root[0]->Tree[1]->id, 5);

    $children = $root[0]->Tree;
    $this->assertEquals((int)$children[0]->id, 3);
    $this->assertEquals($children[0]->name, 'A3');
    $this->assertEquals((int)$children[1]->id, 5);
    $this->assertEquals($children[1]->name, 'A5');
  }

  public function testBridge()
  {
    $stu = new Student(1);
    $this->assertEquals($stu->name, 'tom');

    $this->assertEquals((int)$stu->StudentCourse[0]->course_id, 1);
    $this->assertEquals((int)$stu->StudentCourse[1]->course_id, 2);
    $this->assertEquals((int)$stu->StudentCourse[2]->course_id, 3);

    $this->assertEquals((int)$stu->Course[0]->id, 1);
    $this->assertEquals((int)$stu->Course[1]->id, 2);
    $this->assertEquals((int)$stu->Course[2]->id, 3);

    $this->assertEquals($stu->Course[0]->name, 'Mathematics');
    $this->assertEquals($stu->Course[1]->name, 'Physics');
    $this->assertEquals($stu->Course[2]->name, 'Science');

    $stu = new Student();
    $stu->setConstraint('order', 'id desc');

    $students = $stu->select();
    $this->assertEquals((int)$students[0]->id, 5);
    $this->assertEquals((int)$students[1]->id, 4);

    $this->assertEquals((int)$students[2]->StudentCourse[3]->course_id, 5);
    $this->assertEquals((int)$students[2]->StudentCourse[2]->course_id, 4);
    $this->assertEquals((int)$students[2]->StudentCourse[1]->course_id, 2);
    $this->assertEquals((int)$students[2]->StudentCourse[0]->course_id, 1);

    $this->assertEquals((int)$students[3]->Course[3]->id, 5);
    $this->assertEquals((int)$students[3]->Course[2]->id, 4);
    $this->assertEquals((int)$students[3]->Course[1]->id, 3);
    $this->assertEquals((int)$students[3]->Course[0]->id, 2);

    $this->assertEquals($students[3]->Course[3]->name, 'Psychology');
    $this->assertEquals($students[3]->Course[2]->name, 'Economic');
    $this->assertEquals($students[3]->Course[1]->name, 'Science');
    $this->assertEquals($students[3]->Course[0]->name, 'Physics');

    //-----------------------------------------------------------------

    $course = new Course();
    $course->setConstraint('order', 'id');

    $cs = $course->select();

    $constraint = array('limit' => 100, 'order' => 'student_id');

    foreach ($cs as $c) {
      $c->setChildConstraint($constraint);
      $c->getChild('Student', 'StudentCourse');
    }

    $this->assertEquals($cs[0]->Student[0]->name, 'tom');
    $this->assertEquals($cs[0]->Student[1]->name, 'bob');
    $this->assertEquals($cs[0]->Student[2]->name, 'ameri');

    $this->assertEquals($cs[2]->Student[0]->name, 'tom');
    $this->assertEquals($cs[2]->Student[1]->name, 'john');
    $this->assertEquals($cs[2]->Student[2]->name, 'marcy');
    $this->assertEquals($cs[2]->Student[3]->name, 'ameri');
  }

  public function testJoinSelect()
  {
    $cols = array();
    $cols['test']  = array('id', 'name', 'blood', 'test2_id');
    $cols['test2'] = array('id', 'name', 'test3_id');
    $cols['test3'] = array('id', 'name');

    $pair = array('test:test2', 'test2:test3');

    $test = new Test();
    $test->sconst('order', 'test.id');
    $res  = $test->selectJoin($pair, $cols);

    $test1 = $res[0];
    $test2 = $res[1];
    $test3 = $res[2];
    $test4 = $res[3];
    $test5 = $res[4];
    $test6 = $res[5];

    $this->assertEquals((int)$test1->id, 1);
    $this->assertEquals((int)$test2->id, 2);
    $this->assertEquals((int)$test3->id, 3);
    $this->assertEquals((int)$test4->id, 4);
    $this->assertEquals((int)$test5->id, 5);
    $this->assertEquals((int)$test6->id, 6);

    $this->assertEquals($test1->name, 'tanaka');
    $this->assertEquals($test2->name, 'yo_shida');
    $this->assertEquals($test3->name, 'uchida');
    $this->assertEquals($test4->name, 'ueda');
    $this->assertEquals($test5->name, 'seki');
    $this->assertEquals($test6->name, 'uchida');

    $this->assertEquals((int)$test1->test2->id, 1);
    $this->assertEquals((int)$test2->test2->id, 2);
    $this->assertEquals((int)$test3->test2->id, 1);
    $this->assertEquals((int)$test4->test2->id, 3);
    $this->assertEquals((int)$test5->test2->id, 3);
    $this->assertEquals((int)$test6->test2->id, 1);

    $this->assertEquals((int)$test1->test2->test3->id, 2);
    $this->assertEquals((int)$test2->test2->test3->id, 1);
    $this->assertEquals((int)$test3->test2->test3->id, 2);
    $this->assertEquals((int)$test4->test2->test3->id, 2);
    $this->assertEquals((int)$test5->test2->test3->id, 2);
    $this->assertEquals((int)$test6->test2->test3->id, 2);

    $this->assertEquals($test1->test2->test3->name, 'test32');
    $this->assertEquals($test2->test2->test3->name, 'test31');
    $this->assertEquals($test3->test2->test3->name, 'test32');
    $this->assertEquals($test4->test2->test3->name, 'test32');
    $this->assertEquals($test5->test2->test3->name, 'test32');
    $this->assertEquals($test6->test2->test3->name, 'test32');
  }

  public function testOrder()
  {
    $ol = new OrderLine();
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

    $ol = new OrderLine();
    $ol->item_id(3);
    $last = $ol->getLast('amount');
    $this->assertEquals((int)$last->amount, 9000);

    $ol->item_id(3);
    $first = $ol->getFirst('amount');
    $this->assertEquals((int)$first->amount, 500);

    $ol = new OrderLine();
    $ol->customer_order_id = 1;
    $ol->amount  = 100000;
    $ol->item_id = 1;
    $ol->save();

    $ol = new OrderLine();
    $ol->item_id(1);
    $ol->customer_order_id(1);
    $this->assertEquals($ol->getCount(), 3);

    $ol = new OrderLine();

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
    $user->cconst('limit', 100);
    $user->getChild('bbs');
    $this->assertEquals(count($user->bbs), 5);

    $user = new Users(1);
    $user->cconst('limit', 100);
    $user->ccond('body', 'body13');
    $user->getChild('bbs');
    $this->assertEquals(count($user->bbs), 1);

    $user = new Users(2);
    $user->cconst('limit', 100);
    $user->ccond('OR_body', array('body21', 'body23'));
    $user->getChild('bbs');
    $this->assertEquals(count($user->bbs), 2);

    $bbs = new Bbs();
    $bbs->save(array('users_id' => 4));

    $user = new Users(4);
    $user->cconst('limit', 100);
    $user->ccond('OR_title', array('title41', 'null'));
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
    $ol = new OrderLine();
    $ol->sconst('order', 'id');

    $keys   = array('amount', 'item_id');
    $values = array('> 9000', '2');
    $ol->OR_(array($keys, $values));

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
    $data[] = array('id' => 1, 'text' => 'trans1');
    $data[] = array('id' => 2, 'text' => 'trans2');
    $data[] = array('id' => 3, 'text' => 'trans3');

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
    $trans1->cconst('limit', 10);
    $trans1->getChild('Trans2');
    $this->assertEquals(count($trans1->Trans2) , 3);

    $trans1 = new Trans1(1);
    $trans1->cconst('limit', 10);
    $trans1->ccond('text', 'trans24');
    $trans1->getChild('Trans2');
    $this->assertEquals(count($trans1->Trans2) , 1);

    $trans1 = new Trans1(3);
    $trans1->cconst('limit', 10);
    $trans1->ccond('text', 'trans24');
    $trans1->getChild('Trans2');
    $this->assertEquals(count($trans1->Trans2) , 0);

    $trans1 = new Trans1(2);
    $trans1->cconst('limit', 10);
    $trans1->getChild('Trans2');
    $this->assertEquals(count($trans1->Trans2) , 1);

    $trans1->execute("DELETE FROM trans1");
    $trans2->execute("DELETE FROM trans2");

    //-------------------------------------------------------------------

    $trans1 = new Trans1(); // connection1
    $trans1->begin();

    $trans1->save(array('id' => 1, 'text' => 'trans1'));
    $trans1->save(array('id' => 2, 'text' => 'trans2'));
    $trans1->save(array('id' => 3, 'text' => 'trans3'));

    $trans2 = new Trans2(); // connection2
    $data = array();
    $data[] = array('trans1_id' => 3, 'text' => 'trans21');
    $data[] = array('trans1_id' => 3, 'text' => 'trans22');
    $data[] = array('trans1_id' => 2, 'text' => 'trans23');
    $data[] = array('trans1_id' => 1, 'text' => 'trans24');
    $data[] = array('trans1_id' => 1, 'text' => 'trans25');
    $data[] = array('trans1_id' => 1, 'texx' => 'trans26');  // <- Error && rollback()

    try {
      @$trans2->multipleInsert($data);
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
    $colsName = $test->getColumnNames();

    $this->assertEquals($colsName[0], 'id');
    $this->assertEquals($colsName[1], 'name');
    $this->assertEquals($colsName[2], 'blood');
    $this->assertEquals($colsName[3], 'test2_id');

    $test = new Test();
    $colsName = $test->getColumnNames('seq');

    $this->assertEquals($colsName[0], 'id');
    $this->assertEquals($colsName[1], 'text');
  }

  public function testSchema()
  {
    $st = new SchemaTest();
    $schema = $st->getTableSchema();

    $id1  = $schema->id1;
    $id2  = $schema->id2;
    $num  = $schema->num;
    $fnum = $schema->fnum;
    $dnum = $schema->dnum;
    $str  = $schema->str;
    $text = $schema->text;
    $bl   = $schema->bl;
    $date = $schema->date;
    $dt   = $schema->dt;

    $dbname = Sabel_DB_Connection::getDB($st->getConnectName());

    $this->assertEquals($id1->type, Sabel_DB_Schema_Const::INT);
    $this->assertTrue($id1->primary);
    if ($dbname !== 'sqlite') $this->assertTrue($id1->increment);
    $this->assertEquals($id1->max,  9223372036854775807);
    $this->assertEquals($id1->min, -9223372036854775808);
    @$this->assertEquals($id1->default, null);

    $this->assertEquals($id2->type, Sabel_DB_Schema_Const::INT);
    $this->assertTrue($id2->primary);
    $this->assertFalse($id2->increment);
    $this->assertEquals($id2->max,  2147483647);
    $this->assertEquals($id2->min, -2147483648);
    @$this->assertEquals($id2->default, null);

    $this->assertEquals($num->type, Sabel_DB_Schema_Const::INT);
    $this->assertFalse($num->primary);
    $this->assertFalse($num->increment);
    $this->assertEquals($num->max,  2147483647);
    $this->assertEquals($num->min, -2147483648);
    $this->assertEquals($num->default, 10);

    $this->assertEquals($fnum->type, Sabel_DB_Schema_Const::FLOAT);
    $this->assertEquals($fnum->max,  3.4028235E38);
    $this->assertEquals($fnum->min, -3.4028235E38);
    $this->assertFalse($fnum->primary);
    $this->assertFalse($fnum->notNull);
    $this->assertFalse($fnum->increment);
    @$this->assertEquals($fnum->default, null);

    $this->assertEquals($dnum->type, Sabel_DB_Schema_Const::DOUBLE);
    $this->assertEquals($dnum->max,  1.79769E308);
    $this->assertEquals($dnum->min, -1.79769E308);
    $this->assertFalse($dnum->primary);
    $this->assertFalse($dnum->notNull);
    $this->assertFalse($dnum->increment);
    @$this->assertEquals($dnum->default, null);

    $this->assertEquals($str->type, Sabel_DB_Schema_Const::STRING);
    $this->assertEquals($str->max, 64);
    $this->assertFalse($str->primary);
    $this->assertFalse($str->notNull);
    $this->assertFalse($str->increment);
    $this->assertEquals($str->default, 'test');

    $this->assertEquals($text->type, Sabel_DB_Schema_Const::TEXT);
    $this->assertFalse($text->primary);
    $this->assertFalse($text->notNull);
    $this->assertFalse($text->increment);
    @$this->assertEquals($text->default, null);

    $this->assertEquals($bl->type, Sabel_DB_Schema_Const::BOOL);
    $this->assertFalse($bl->primary);
    $this->assertFalse($bl->notNull);
    $this->assertFalse($bl->increment);
    $this->assertTrue($bl->default);

    $this->assertEquals($date->type, Sabel_DB_Schema_Const::DATE);
    $this->assertFalse($date->primary);
    $this->assertFalse($date->increment);
    $this->assertFalse($date->notNull);
    @$this->assertEquals($date->default, null);

    $this->assertEquals($dt->type, Sabel_DB_Schema_Const::TIMESTAMP);
    $this->assertFalse($dt->primary);
    $this->assertFalse($dt->increment);
    $this->assertTrue($dt->notNull);
    @$this->assertEquals($dt->default, null);
  }

  public function testWhereNot()
  {
    $test = new Test();
    $test->id(1, 'NOT');
    $tests = $test->select();

    $this->assertEquals($tests[0]->name, 'yo_shida');
    $this->assertEquals($tests[1]->name, 'uchida');
    $this->assertEquals($tests[2]->name, 'ueda');
    $this->assertEquals($tests[3]->name, 'seki');
    $this->assertEquals($tests[4]->name, 'uchida');

    $test = new Test();
    $test->IN_id(array(2,3,5));
    $tests = $test->select();

    $this->assertEquals($tests[0]->name, 'yo_shida');
    $this->assertEquals($tests[1]->name, 'uchida');
    $this->assertEquals($tests[2]->name, 'seki');

    $test = new Test();
    $test->IN_id(array(2,3,5), 'NOT');
    $tests = $test->select();

    $this->assertEquals($tests[0]->name, 'tanaka');
    $this->assertEquals($tests[1]->name, 'ueda');
    $this->assertEquals($tests[2]->name, 'uchida');

    $test = new Test();
    $test->BET_id(array(2, 4));
    $tests = $test->select();

    $this->assertEquals($tests[0]->name, 'yo_shida');
    $this->assertEquals($tests[1]->name, 'uchida');
    $this->assertEquals($tests[2]->name, 'ueda');

    $test = new Test();
    $test->BET_id(array(2, 4), 'NOT');
    $tests = $test->select();

    $this->assertEquals($tests[0]->name, 'tanaka');
    $this->assertEquals($tests[1]->name, 'seki');
    $this->assertEquals($tests[2]->name, 'uchida');

    $test = new Test();
    $test->LIKE_name(array('%na%', false));
    $tests = $test->select();

    $this->assertEquals($tests[0]->name, 'tanaka');

    $test = new Test();
    $test->LIKE_name(array('%na%', false), 'NOT');
    $tests = $test->select();

    $this->assertEquals($tests[0]->name, 'yo_shida');
    $this->assertEquals($tests[1]->name, 'uchida');
    $this->assertEquals($tests[2]->name, 'ueda');
    $this->assertEquals($tests[3]->name, 'seki');
    $this->assertEquals($tests[4]->name, 'uchida');
  }

  public function testResultSet()
  {
    $executer  = new Sabel_DB_Executer('default');
    $executer->getStatement()->setBasicSQL('SELECT * FROM test');
    $condition = new Sabel_DB_Condition('LIKE_name', array('%na%', false), 'NOT');
    $executer->setCondition($condition);
    $resultSet = $executer->execute();
    $resultObj = $resultSet->fetchAll(Sabel_DB_Driver_ResultSet::OBJECT);

    $this->assertEquals($resultObj[0]->name, 'yo_shida');
    $this->assertEquals($resultObj[1]->name, 'uchida');
    $this->assertEquals($resultObj[2]->name, 'ueda');
    $this->assertEquals($resultObj[3]->name, 'seki');
    $this->assertEquals($resultObj[4]->name, 'uchida');
  }

  public function testClear()
  {
    Sabel_DB_SimpleCache::clear();
    Sabel_DB_Connection::closeAll();
  }
}

class MapperDefault extends Sabel_DB_Mapper
{

}

class Test extends MapperDefault
{
  protected $withParent = true;
}

class Test2 extends MapperDefault
{

}

class Test3 extends MapperDefault
{

}

class Infinite1 extends MapperDefault
{

}

class Infinite2 extends MapperDefault
{

}

class Seq extends MapperDefault
{

}

class Customer extends MapperDefault
{
  protected $myChildren = array('CustomerOrder', 'CustomerTelephone');
  protected $defChildConstraints = array('limit' => 10);

  public function __construct($param1 = null, $param2 = null)
  {
    $this->setChildConstraint('CustomerOrder', array('limit' => 10));
    parent::__construct($param1, $param2);
  }
}

class CustomerOrder extends MapperDefault
{
  protected $myChildren = 'OrderLine';

  public function __construct($param1 = null, $param2 = null)
  {
    $this->setChildConstraint('OrderLine', array('limit' => 10));
    parent::__construct($param1, $param2);
  }
}

class OrderLine extends MapperDefault
{

}

class CustomerTelephone extends MapperDefault
{

}

class Tree extends Sabel_DB_Tree
{

}

class Bridge_Base extends Sabel_DB_Bridge
{
  protected $bridgeTable = 'StudentCourse';
}

class Student extends Bridge_Base
{
  protected $myChildren  = 'Course';

  public function __construct($param1 = null, $param2 = null)
  {
    $this->cconst(array('limit' => 100, 'order' => 'course_id'));
    parent::__construct($param1, $param2);
  }
}

class Course extends Bridge_Base
{

}

class StudentCourse extends MapperDefault
{

}

class Trans1 extends MapperDefault
{

}

class Trans2 extends Sabel_DB_Mapper
{
  protected $connectName = 'default2';
}

class Users extends MapperDefault
{

}

class Bbs extends MapperDefault
{

}

class Status extends MapperDefault
{

}

class SchemaTest extends MapperDefault
{

}
