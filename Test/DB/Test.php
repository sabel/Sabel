<?php

class Test_DB_Test extends SabelTestCase
{
  public static $db = '';
  public static $TABLES = array('basic', 'users', 'city', 'country', 'company', 'planet',
                                'test_for_like', 'test_condition', 'blog', 'mail',
                                'customer_order', 'classification', 'favorite_item',
                                'student', 'course', 'student_course', 'timer', 'child');

  public function testBasic()
  {
    $basic = MODEL('Basic');
    $basic->id   = 1;
    $basic->name = 'basic name1';
    $basic->save();

    $basic = MODEL('Basic');
    $basic->insert(array('id' => 2, 'name' => 'basic name2'));

    $basic = MODEL('Basic');
    $model = $basic->selectOne(1);
    $this->assertEquals($model->name, 'basic name1');

    $model = MODEL('Basic')->selectOne('name', 'basic name2');
    $this->assertEquals((int)$model->id, 2);
  }

  public function testParent()
  {
    $shop = MODEL('Company');
    $shop->insert(array('id' => 1, 'name' => 'tokyo-company1',     'city_id' => 1));
    $shop->insert(array('id' => 2, 'name' => 'tokyo-company2',     'city_id' => 1));
    $shop->insert(array('id' => 3, 'name' => 'osaka-company1',     'city_id' => 2));
    $shop->insert(array('id' => 4, 'name' => 'osaka-company2',     'city_id' => 2));
    $shop->insert(array('id' => 5, 'name' => 'san diego-company1', 'city_id' => 3));
    $shop->insert(array('id' => 6, 'name' => 'san diego-company2', 'city_id' => 3));
    $shop->insert(array('id' => 7, 'name' => 'rondon-company1',    'city_id' => 4));
    $shop->insert(array('id' => 8, 'name' => 'rondon-company2',    'city_id' => 4));

    $data = array();
    $data[] = array('id' => 1, 'name' => 'username1', 'email' => 'user1@example.com',
                    'city_id' => 4, 'company_id' => 7);
    $data[] = array('id' => 2, 'name' => 'username2', 'email' => 'user2@example.com',
                    'city_id' => 3, 'company_id' => 5);
    $data[] = array('id' => 3, 'name' => 'username3', 'email' => 'user3@example.com',
                    'city_id' => 2, 'company_id' => 4);
    $data[] = array('id' => 4, 'name' => 'username4', 'email' => 'user4@example.com',
                    'city_id' => 1, 'company_id' => 2);

    $users = new Users();
    $users->arrayInsert($data);

    $data = array();
    $data[] = array('id' => 1, 'name' => 'tokyo',     'classification_id' => 1, 'country_id' => 1);
    $data[] = array('id' => 2, 'name' => 'osaka',     'classification_id' => 2, 'country_id' => 1);
    $data[] = array('id' => 3, 'name' => 'san diego', 'classification_id' => 2, 'country_id' => 2);
    $data[] = array('id' => 4, 'name' => 'rondon',    'classification_id' => 1, 'country_id' => 3);

    $city = MODEL('City');
    $city->arrayInsert($data);

    $city = MODEL('Classification');
    $city->insert(array('id' => 1, 'class_name' => 'classname1'));
    $city->insert(array('id' => 2, 'class_name' => 'classname2'));

    $data = array();
    $data[] = array('id' => 1, 'planet_id' => 1, 'name' => 'japan');
    $data[] = array('id' => 2, 'planet_id' => 1, 'name' => 'usa');
    $data[] = array('id' => 3, 'planet_id' => 1, 'name' => 'england');

    $country = new Country();
    $country->arrayInsert($data);

    $planet = MODEL('Planet');
    $planet->id = 1;
    $planet->name = 'earth';
    $planet->save();

    $model = new Users();
    $model->setConstraint('order', 'users.id');
    $users = $model->select();

    $user1 = $users[0];
    $user2 = $users[1];
    $user3 = $users[2];
    $user4 = $users[3];

    $this->assertEquals((int)$user1->id, 1);
    $this->assertEquals((int)$user2->id, 2);
    $this->assertEquals($user3->email, 'user3@example.com');
    $this->assertEquals($user4->email, 'user4@example.com');

    $this->assertEquals($user1->City->name, 'rondon');
    $this->assertEquals($user2->City->name, 'san diego');

    $this->assertEquals((int)$user3->City->country_id, 1);
    $this->assertEquals((int)$user4->City->country_id, 1);
    $this->assertEquals($user3->City->Country->name, 'japan');
    $this->assertEquals($user4->City->Country->name, 'japan');
  }

  public function testLimitation()
  {
    $model = new Users();
    $model->setConstraint('order', 'users.id');
    $users = $model->select();
    $this->assertEquals(count($users), 4);

    $this->assertEquals($users[0]->id, 1);
    $this->assertEquals($users[1]->id, 2);
    $this->assertEquals($users[2]->id, 3);
    $this->assertEquals($users[3]->id, 4);

    $model = new Users();
    $model->setConstraint('order', 'users.id');
    $model->setConstraint('limit', 2);
    $users = $model->select();
    $this->assertEquals(count($users), 2);

    $this->assertEquals($users[0]->id, 1);
    $this->assertEquals($users[1]->id, 2);

    $model = new Users();
    $model->setConstraint('order', 'users.id');
    $model->setConstraint('offset', 1);
    $users = $model->select();
    $this->assertEquals(count($users), 3);

    $this->assertEquals($users[0]->id, 2);
    $this->assertEquals($users[1]->id, 3);
    $this->assertEquals($users[2]->id, 4);

    $model = new Users();
    $model->setConstraint('order', 'users.id DESC');
    $model->setConstraint('offset', 2);
    $model->setConstraint('limit',  2);
    $users = $model->select();
    $this->assertEquals(count($users), 2);

    $this->assertEquals($users[0]->id, 2);
    $this->assertEquals($users[1]->id, 1);
  }

  public function testLike()
  {
    $model = MODEL('TestForLike');
    $model->insert(array('string' => 'aaa'));
    $model->insert(array('string' => 'aa_'));
    $model->insert(array('string' => 'aba'));

    $model = MODEL('TestForLike');
    $model->string = 'a%a';
    $newModel = $model->save();

    $this->assertTrue(is_numeric($newModel->id));
    $this->assertTrue(($newModel->id > 0));
    $this->assertEquals($newModel->string, 'a%a');

    $model = MODEL('TestForLike');
    $model->setCondition(new Condition("string", "aa_", LIKE));
    $result = $model->select();

    $this->assertTrue(is_array($result));
    $this->assertEquals(count($result), 1);
    $this->assertEquals($result[0]->string, 'aa_');

    $model = MODEL('TestForLike');
    $model->setCondition(new Condition("string", array("aa_", false), LIKE));
    $result = $model->select();

    $this->assertEquals(count($result), 2);
    $this->assertEquals($result[0]->string, 'aaa');
    $this->assertEquals($result[1]->string, 'aa_');

    $model = MODEL('TestForLike');
    $model->setCondition(new Condition("string", "a%a", LIKE));
    $result = $model->select();

    $this->assertEquals(count($result), 1);
    $this->assertEquals($result[0]->string, 'a%a');

    $model = MODEL('TestForLike');
    $model->setCondition(new Condition("string", array("a%a", false), LIKE));
    $result = $model->select();

    $this->assertEquals(count($result), 3);
    $this->assertEquals($result[0]->string, 'aaa');
    $this->assertEquals($result[1]->string, 'aba');
    $this->assertEquals($result[2]->string, 'a%a');
  }

  public function testCondition()
  {
    $model = MODEL('TestCondition');
    $model->insert(array('status' => true,  'registed' => '2005-10-01 10:10:10', 'point' => 1000));
    $model->insert(array('status' => false, 'registed' => '2005-09-01 10:10:10', 'point' => 2000));
    $model->insert(array('status' => false, 'registed' => '2005-08-01 10:10:10', 'point' => 3000));
    $model->insert(array('status' => true,  'registed' => '2005-07-01 10:10:10', 'point' => 4000));
    $model->insert(array('status' => false, 'registed' => '2005-06-01 10:10:10', 'point' => 5000));
    $model->insert(array('status' => false, 'registed' => '2005-05-01 10:10:10', 'point' => 6000));
    $model->insert(array('status' => true,  'registed' => '2005-04-01 10:10:10', 'point' => 7000));
    $model->insert(array('status' => false, 'registed' => '2005-03-01 10:10:10', 'point' => 8000));
    $model->insert(array('status' => false, 'registed' => '2005-02-01 10:10:10', 'point' => 9000));
    $model->insert(array('status' => true,  'registed' => '2005-01-01 10:10:10', 'point' => 10000));

    $model = MODEL('TestCondition');
    $model->setCondition(new Condition("point", array(">=", 8000), COMPARE));
    $models = $model->select();
    $this->assertEquals(count($models), 3);

    $models = MODEL('TestCondition')->select(new Condition("point", array(">=", 8000), COMPARE));
    $this->assertEquals(count($models), 3);

    $models = MODEL('TestCondition')->select(new Condition("point", array(">", 8000), COMPARE));
    $this->assertEquals(count($models), 2);

    $model = MODEL('TestCondition');
    $manager = $model->loadConditionManager();

    $or = new OrCondition();
    $or->add(new Condition("point", array(">=", 8000), COMPARE));
    $or->add(new Condition("point", array("<=", 3000), COMPARE));
    $manager->add($or);

    $models = $model->select();
    $this->assertEquals(count($models), 6);

    $model = MODEL('TestCondition');
    $manager = $model->loadConditionManager();

    $or = new OrCondition();
    $or->add(new Condition("point", array(">=", 8000), COMPARE));
    $or->add(new Condition("registed", array(">", "2005-08-01 01:01:01"), COMPARE));
    $manager->add($or);

    $model->setConstraint('order', 'id');
    $models = $model->select();
    $this->assertEquals(count($models), 6);

    $model1 = $models[0];
    $model2 = $models[1];
    $model3 = $models[2];
    $model4 = $models[3];
    $model5 = $models[4];
    $model6 = $models[5];

    $this->assertTrue($model1->status);
    $this->assertFalse($model2->status);
    $this->assertFalse($model3->status);

    $this->assertEquals($model4->point, 8000);
    $this->assertEquals($model5->point, 9000);
    $this->assertEquals($model6->point, 10000);

    $model = MODEL('TestCondition');
    $model->setCondition('status', false);
    $models = $model->select();
    $this->assertEquals(count($models), 6);

    $model1 = $models[0];
    $model2 = $models[1];
    $model3 = $models[2];

    $this->assertEquals($model1->point, 2000);
    $this->assertEquals($model2->point, 3000);
    $this->assertEquals($model3->point, 5000);

    $model->unsetConditions();
    $model->setCondition(new Condition("status", false, NOT));
    $models = $model->select();
    $this->assertEquals(count($models), 4);

    $model1 = $models[0];
    $model2 = $models[1];
    $model3 = $models[2];
    $model4 = $models[3];

    $this->assertEquals($model1->point, 1000);
    $this->assertEquals($model2->point, 4000);
    $this->assertEquals($model3->point, 7000);
    $this->assertEquals($model4->point, 10000);

    $model = MODEL('TestCondition');
    $model->setCondition(new Condition("registed",
                                       array("2005-01-01 11:11:11", "2005-05-05 11:11:11"),
                                       BETWEEN));

    $model->setConstraint('order', 'registed');
    $models = $model->select();
    $this->assertEquals(count($models), 4);

    $model1 = $models[0];
    $model2 = $models[1];
    $model3 = $models[2];
    $model4 = $models[3];

    $this->assertEquals($model1->point, 9000);
    $this->assertEquals($model2->point, 8000);
    $this->assertEquals($model3->point, 7000);
    $this->assertEquals($model4->point, 6000);

    $model->unsetConditions();

    $model->setCondition(new Condition("registed",
                                       array("2005-01-01 11:11:11", "2005-05-05 11:11:11"),
                                       BETWEEN, NOT));

    $model->setConstraint('order', 'registed');
    $models = $model->select();
    $this->assertEquals(count($models), 6);

    $model1 = $models[0];
    $model2 = $models[1];

    $this->assertEquals($model1->point, 10000);
    $this->assertEquals($model2->point, 5000);

    $model = MODEL('TestCondition');
    $model->insert(array('status' => false, 'registed' => '2004-12-01 10:10:10'));
    $model->insert(array('status' => false, 'registed' => '2004-11-01 10:10:10'));
    $model->insert(array('status' => true,  'registed' => '2004-10-01 10:10:10', 'point' => 13000));

    $model->setCondition("point", IS_NULL);
    $models = $model->select();
    $this->assertEquals(count($models), 2);

    $model1 = $models[0];
    $model2 = $models[1];

    $this->assertEquals($model1->registed, '2004-12-01 10:10:10');
    $this->assertEquals($model2->registed, '2004-11-01 10:10:10');

    $model->unsetConditions();

    $models = $model->select("point", IS_NOT_NULL);
    $this->assertEquals(count($models), 11);

    $model = MODEL('TestCondition');
    $model->setCondition("point", IS_NOT_NULL);
    $model->setCondition(new Condition("registed", array("<=", "2005-02-01 10:10:10"), COMPARE));
    $models = $model->select();
    $this->assertEquals(count($models), 3);

    $model1 = $models[0];
    $model2 = $models[1];
    $model3 = $models[2];

    $this->assertEquals($model1->registed, '2005-02-01 10:10:10');
    $this->assertEquals($model2->registed, '2005-01-01 10:10:10');
    $this->assertEquals($model3->registed, '2004-10-01 10:10:10');
  }

  public function testTest()
  {
    $model = MODEL('Customer');
    $model->executeQuery('DELETE FROM customer');

    $model->insert(array('id' => 1, 'name' => 'name1'));
    $model->insert(array('id' => 2, 'name' => 'name2'));

    $order = MODEL('CustomerOrder');
    $order->insert(array('customer_id' => 1, 'buy_date' => '2005-01-01 10:10:10', 'amount' => 1000));
    $order->insert(array('customer_id' => 1, 'buy_date' => '2005-02-01 10:10:10', 'amount' => 2000));
    $order->insert(array('customer_id' => 1, 'buy_date' => '2005-03-01 10:10:10', 'amount' => 3000));
    $order->insert(array('customer_id' => 1, 'buy_date' => '2005-04-01 10:10:10', 'amount' => 4000));
    $order->insert(array('customer_id' => 1, 'buy_date' => '2005-05-01 10:10:10', 'amount' => 5000));
    $order->insert(array('customer_id' => 2, 'buy_date' => '2005-06-01 10:10:10', 'amount' => 6000));
    $order->insert(array('customer_id' => 2, 'buy_date' => '2005-07-01 10:10:10', 'amount' => 7000));
    $order->insert(array('customer_id' => 2, 'buy_date' => '2005-08-01 10:10:10', 'amount' => 8000));
  }

  public function testDBRelation()
  {
    $customer = MODEL('Customer')->selectOne(2);
    $orders   = $customer->getChild('CustomerOrder');
    $this->assertEquals(count($orders), 3);

    $order1 = $orders[0];
    $order2 = $orders[1];
    $order3 = $orders[2];

    $this->assertEquals($order1->amount, 6000);
    $this->assertEquals($order2->amount, 7000);
    $this->assertEquals($order3->amount, 8000);

    $model = MODEL('CustomerOrder');
    $model->setConstraint('order', 'buy_date desc');
    $orders = $model->select();
    $this->assertEquals(count($orders), 8);

    $order7 = $orders[1];
    $order6 = $orders[2];
    $order5 = $orders[3];
    $order4 = $orders[4];

    $this->assertEquals($order7->buy_date, '2005-07-01 10:10:10');
    $this->assertEquals($order6->buy_date, '2005-06-01 10:10:10');
    $this->assertEquals($order5->buy_date, '2005-05-01 10:10:10');
    $this->assertEquals($order4->buy_date, '2005-04-01 10:10:10');

    $order = MODEL('CustomerOrder');
    $order->customer_id = 2;
    $order->buy_date = '2005-08-01 10:10:10';
    $order->amount   = 9000;
    $order->save();

    $orders = MODEL('Customer')->selectOne(2)->getChild('CustomerOrder');
    $this->assertEquals(count($orders), 4);

    $order = MODEL('CustomerOrder');
    $order->setParents(array('Customer'));
    $order->setConstraint('order', 'CustomerOrder.id');
    $orders = $order->select();

    $this->assertEquals($orders[0]->Customer->id, 1);

    $this->assertEquals($orders[0]->customer_id, 1);
    $this->assertEquals($orders[0]->buy_date, '2005-01-01 10:10:10');
    $this->assertEquals($orders[0]->amount, 1000);

    $this->assertEquals($orders[1]->customer_id, 1);
    $this->assertEquals($orders[1]->buy_date, '2005-02-01 10:10:10');
    $this->assertEquals($orders[1]->amount, 2000);

    $this->assertEquals($orders[5]->customer_id, 2);
    $this->assertEquals($orders[5]->buy_date, '2005-06-01 10:10:10');
    $this->assertEquals($orders[5]->amount, 6000);

    $this->assertEquals($orders[5]->Customer->id, 2);
  }

  public function testJoin()
  {
    $users = new Users();
    $users->setConstraint("order", "Users.id");

    $joiner = new Sabel_DB_Join($users);

    $join = new Sabel_DB_Join_Relation(MODEL("City"));
    $join->add(MODEL("Country"), null, null, array("id" => "id", "fkey" => "country_id"));

    $joiner->add($join, null, null, array("id" => "id", "fkey" => "city_id"));

    $users = $joiner->join();
    $user1 = $users[0];
    $user2 = $users[1];
    $user3 = $users[2];
    $user4 = $users[3];

    $this->assertEquals($user1->name, 'username1');
    $this->assertEquals($user2->name, 'username2');

    $this->assertEquals($user3->City->name, 'osaka');
    $this->assertEquals($user4->City->name, 'tokyo');

    $this->assertEquals($user1->City->Country->name, 'england');
    $this->assertEquals($user2->City->Country->name, 'usa');
  }

  public function testMoreJoin()
  {
    $users = new Users();
    $users->setConstraint("order", "users.id");

    $joiner = new Sabel_DB_Join($users);
    $joiner->add(MODEL("Company"), null, null, array("id" => "id", "fkey" => "company_id"));

    $join = new Sabel_DB_Join_Relation(MODEL("City"));
    $join->add(MODEL("Country"), null, null, array("id" => "id", "fkey" => "country_id"))
         ->add(MODEL("Classification"), null, null, array("id" => "id", "fkey" => "classification_id"));

    $joiner->add($join, null, null, array("id" => "id", "fkey" => "city_id"));

    $users = $joiner->join();
    $this->assertEquals(count($users), 4);

    $user1 = $users[0];
    $user2 = $users[1];
    $user3 = $users[2];
    $user4 = $users[3];

    $this->assertEquals($user1->name, 'username1');
    $this->assertEquals($user4->name, 'username4');

    $this->assertEquals($user2->City->name, 'san diego');
    $this->assertEquals($user3->City->name, 'osaka');

    $this->assertEquals($user1->City->Classification->class_name, 'classname1');
    $this->assertEquals($user2->City->Classification->class_name, 'classname2');
    $this->assertEquals($user3->City->Classification->class_name, 'classname2');
    $this->assertEquals($user4->City->Classification->class_name, 'classname1');

    $this->assertEquals($user1->City->Country->name, 'england');
    $this->assertEquals($user2->City->Country->name, 'usa');
    $this->assertEquals($user3->City->Country->name, 'japan');
    $this->assertEquals($user4->City->Country->name, 'japan');

    $this->assertEquals($user4->Company->id, 2);
    $this->assertEquals($user4->City->id, 1);
    $this->assertEquals($user4->City->name, 'tokyo');
    $this->assertEquals($user4->Company->name, 'tokyo-company2');
  }

  public function testOyanoOyanoOya()
  {
    $user = new Users();
    $user->setConstraint("order", "Users.id");
    $joiner = new Sabel_DB_Join($user);

    $country = new Sabel_DB_Join_Relation(MODEL("Country"));
    $country->add(MODEL("Planet"), null, null, array("id" => "id", "fkey" => "planet_id"));

    $city = new Sabel_DB_Join_Relation(MODEL("City"));
    $city->add($country, null, null, array("id" => "id", "fkey" => "country_id"));

    $joiner->add($city, null, null, array("id" => "id", "fkey" => "city_id"));
    $users = $joiner->join();

    $this->assertEquals(count($users), 4);

    $user1 = $users[0];
    $user2 = $users[1];
    $user3 = $users[2];
    $user4 = $users[3];

    $this->assertEquals($user1->name, 'username1');
    $this->assertEquals($user4->name, 'username4');

    $this->assertEquals($user2->City->name, 'san diego');
    $this->assertEquals($user3->City->name, 'osaka');

    $this->assertEquals($user1->City->Country->name, 'england');
    $this->assertEquals($user2->City->Country->name, 'usa');
    $this->assertEquals($user3->City->Country->name, 'japan');
    $this->assertEquals($user4->City->Country->name, 'japan');

    $this->assertEquals($user4->City->id, 1);
    $this->assertEquals($user4->City->name, 'tokyo');

    $this->assertEquals($user1->City->Country->Planet->id, 1);
    $this->assertEquals($user1->City->Country->Planet->name, "earth");
  }

  public function testJoinAlias()
  {
    $data = array();
    $data[] = array('id' => 1, 'sender_id' => 1, 'recipient_id' => 2, 'subject' => 'subject1');
    $data[] = array('id' => 2, 'sender_id' => 1, 'recipient_id' => 3, 'subject' => 'subject2');
    $data[] = array('id' => 3, 'sender_id' => 2, 'recipient_id' => 1, 'subject' => 'subject3');
    $data[] = array('id' => 4, 'sender_id' => 3, 'recipient_id' => 4, 'subject' => 'subject4');
    $data[] = array('id' => 5, 'sender_id' => 1, 'recipient_id' => 4, 'subject' => 'subject5');
    $data[] = array('id' => 6, 'sender_id' => 2, 'recipient_id' => 3, 'subject' => 'subject6');

    $mail = MODEL("Mail");
    $mail->arrayInsert($data);

    $mail = MODEL("Mail");
    $mail->setConstraint("order", "Mail.id");
    $joiner = new Sabel_DB_Join($mail);

    $user = MODEL("Users");
    $join = new Sabel_DB_Join_Relation($user);
    $join->add(MODEL("City"), null, null, array("id" => "id", "fkey" => "city_id"));

    $joiner->add($join, null, "FromUser", array("id" => "id", "fkey" => "sender_id"));
    $joiner->add($user, null, "ToUser", array("id" => "id", "fkey" => "recipient_id"));

    $results = $joiner->join();
    $this->assertEquals(count($results), 6);

    $mail1 = $results[0];
    $mail3 = $results[2];
    $mail5 = $results[4];

    $this->assertTrue(is_object($mail1->FromUser));
    $this->assertTrue(is_object($mail1->FromUser->City));
    $this->assertTrue(is_object($mail1->ToUser));
    $this->assertTrue(is_object($mail3->FromUser));
    $this->assertTrue(is_object($mail3->FromUser->City));
    $this->assertTrue(is_object($mail5->ToUser));

    $this->assertEquals($mail1->FromUser->id, 1);
    $this->assertEquals($mail1->ToUser->id, 2);
    $this->assertEquals($mail3->FromUser->id, 2);
    $this->assertEquals($mail3->ToUser->id, 1);
    $this->assertEquals($mail5->FromUser->id, 1);
    $this->assertEquals($mail5->ToUser->id, 4);

    $this->assertEquals($mail3->FromUser->name, "username2");
    $this->assertEquals($mail3->ToUser->name, "username1");
  }

  public function testRemove()
  {
    $model = MODEL('TestCondition');
    $this->assertEquals($model->getCount(), 13);

    $model->delete('point', 1000);

    $model = MODEL('TestCondition');
    $this->assertEquals($model->getCount(), 12);
    $model->unsetConditions();

    $model->setCondition('point', IS_NULL);
    $this->assertEquals($model->getCount(), 2);

    MODEL('TestCondition')->delete('point', IS_NULL);

    $model = MODEL('TestCondition');
    $this->assertEquals($model->getCount(), 10);

    $model = MODEL('TestCondition');
    $manager = $model->loadConditionManager();
    $manager->clear();

    $or = new OrCondition();
    $or->add(new Condition("point", 10000));
    $or->add(new Condition("point", array("<=", 4000), COMPARE));
    $manager->add($or);
    $model->delete();

    $model = MODEL('TestCondition');
    $this->assertEquals($model->getCount(), 6);

    $models = $model->select();
    $model1 = $models[0];
    $model2 = $models[1];
    $model3 = $models[2];
    $model4 = $models[3];
    $model5 = $models[4];
    $model6 = $models[5];

    $this->assertEquals($model1->registed, '2005-06-01 10:10:10');
    $this->assertEquals($model2->registed, '2005-05-01 10:10:10');
    $this->assertEquals($model3->registed, '2005-04-01 10:10:10');
    $this->assertEquals($model4->registed, '2005-03-01 10:10:10');
    $this->assertEquals($model5->registed, '2005-02-01 10:10:10');
    $this->assertEquals($model6->registed, '2004-10-01 10:10:10');

    $customer = MODEL('Customer')->selectOne(1);
    $customer->delete();

    $customer = MODEL('Customer')->selectOne(2);
    $customer->delete();
  }

  public function testCascadeDelete()
  {
    $deleter = new Sabel_DB_Model_CascadeDelete("Country", 1);
    $deleter->execute(new CountryCascadeDelete());

    $country   = new Country();
    $countries = $country->select();

    $this->assertEquals(count($countries), 2);
    $this->assertEquals($countries[0]->name, 'usa');
    $this->assertEquals($countries[1]->name, 'england');

    $model  = MODEL('City');
    $cities = $model->select();

    $this->assertEquals(count($cities), 2);
    $this->assertEquals($cities[0]->name, 'san diego');
    $this->assertEquals($cities[1]->name, 'rondon');

    $users = new Users();
    $users->setConstraint('order', 'users.id');
    $users = $users->select();

    $this->assertEquals(count($users), 2);
    $this->assertEquals($users[0]->name, 'username1');
    $this->assertEquals($users[1]->name, 'username2');
  }

  public function testTransaction()
  {
    MODEL('CustomerOrder')->executeQuery('DELETE FROM customer_order');

    $customers = MODEL('Customer')->select();
    $this->assertFalse($customers);

    $orders = MODEL('CustomerOrder')->select();
    $this->assertFalse($orders);

    $model = Sabel_DB_Transaction::load("CustomerOrder");
    $model->insert(array('customer_id' => 1, 'buy_date' => '1000-01-01 01:01:01', 'amount' => 1000));
    $model->insert(array('customer_id' => 1, 'buy_date' => '1000-01-01 01:01:01', 'amount' => 1000));
    $model->insert(array('customer_id' => 1, 'buy_date' => '1000-01-01 01:01:01', 'amount' => 1000));

    $model = Sabel_DB_Transaction::load("Customer");
    $model->insert(array('id' => 1, 'name' => 'name'));
    $model->insert(array('id' => 2, 'name' => 'name'));

    try {
      // 'nama' not found -> execute rollback.
      @$model->insert(array('id' => 3, 'nama' => 'name'));
    } catch (Exception $e) {

    }

    // not execute.
    Sabel_DB_Transaction::commit();

    $customers = MODEL('Customer')->select();
    $this->assertFalse($customers);

    $orders = MODEL('CustomerOrder')->select();
    $this->assertFalse($orders);
  }

  public function testDatabasesCasecadeDelete()
  {
    $model = MODEL('CustomerOrder');
    $model->insert(array('customer_id' => 1, 'buy_date' => '1000-01-01 01:01:01', 'amount' => 1000));
    $model->insert(array('customer_id' => 1, 'buy_date' => '1000-01-01 01:01:01', 'amount' => 1000));
    $model->insert(array('customer_id' => 1, 'buy_date' => '1000-01-01 01:01:01', 'amount' => 1000));
    $model->insert(array('customer_id' => 2, 'buy_date' => '2000-02-02 02:02:02', 'amount' => 2000));

    $model = MODEL('Customer');
    $model->insert(array('id' => 1, 'name' => 'name1'));
    $model->insert(array('id' => 2, 'name' => 'name2'));
    $model->insert(array('id' => 3, 'name' => 'name3'));

    $deleter = new Sabel_DB_Model_CascadeDelete("Customer", 1);
    $deleter->execute(new CustomerCascadeDelete());

    $this->assertEquals(MODEL('Customer')->getCount(), 2);

    $model = MODEL('Customer')->select();
    $cus1  = $model[0];
    $cus2  = $model[1];

    $this->assertEquals($cus1->id, 2);
    $this->assertEquals($cus1->name, 'name2');
    $this->assertEquals($cus2->id, 3);
    $this->assertEquals($cus2->name, 'name3');

    $model = MODEL('CustomerOrder')->select();
    $this->assertEquals(count($model), 1);
    $this->assertEquals($model[0]->customer_id, 2);
    $this->assertEquals($model[0]->buy_date, '2000-02-02 02:02:02');
    $this->assertEquals($model[0]->amount, 2000);

    MODEL('Customer')->executeQuery('DELETE FROM customer');
    MODEL('CustomerOrder')->executeQuery('DELETE FROM customer_order');
  }

  public function testUpdate()
  {
    $model = MODEL('Customer');
    $model->insert(array('id' => 1, 'name' => 'name1'));
    $model->insert(array('id' => 2, 'name' => 'name2'));

    $customer = MODEL('Customer')->selectOne(1);
    $this->assertEquals($customer->name, 'name1');

    $customer->name = 'new name1';
    $customer = $customer->save();

    $this->assertEquals((int)$customer->id, 1);
    $this->assertEquals($customer->name, 'new name1');

    $customer = MODEL('Customer')->selectOne(1);
    $this->assertEquals($customer->name, 'new name1');

    $customer = MODEL('Customer')->selectOne(100);
    $this->assertFalse($customer->isSelected());

    $customer->name = 'name100';
    $customer = $customer->save();
    $this->assertEquals((int)$customer->id, 100);
    $this->assertEquals($customer->name, 'name100');

    $customer = MODEL('Customer')->selectOne(100);
    $this->assertTrue($customer->isSelected());
    $this->assertEquals($customer->name, 'name100');

    $model = MODEL('CustomerOrder');
    $model->setCondition(5);
    $model->setCondition('customer_id', 10);
    $order = $model->selectOne();
    $this->assertFalse($order->isSelected());

    $order->buy_date = '1999-01-01 12:34:55';
    $order->amount   = 9999;
    $order->save();

    $order = MODEL('CustomerOrder')->selectOne(5);
    $this->assertTrue($order->isSelected());

    $this->assertEquals($order->id, 5);
    $this->assertEquals($order->customer_id, 10);
    $this->assertEquals($order->buy_date, '1999-01-01 12:34:55');
    $this->assertEquals($order->amount, 9999);
  }

  public function testSchema()
  {
    $model  = MODEL('SchemaTest');
    $schema = $model->getSchema();

    $id = $schema->id;
    $nm = $schema->name;
    $bl = $schema->bl;
    $dt = $schema->dt;
    $ft = $schema->ft_val;
    $db = $schema->db_val;
    $tx = $schema->tx;

    $this->assertEquals($id->type, Sabel_DB_Type::INT);
    $this->assertEquals($id->max,  2147483647);
    $this->assertEquals($id->min, -2147483648);
    $this->assertFalse($id->nullable);
    $this->assertTrue($id->increment);
    $this->assertTrue($id->primary);

    $this->assertEquals($nm->type, Sabel_DB_Type::STRING);
    $this->assertEquals($nm->max, 128);
    $this->assertFalse($nm->nullable);
    $this->assertFalse($nm->increment);
    $this->assertFalse($nm->primary);
    $this->assertEquals($nm->default, 'test');

    $this->assertEquals($bl->type, Sabel_DB_Type::BOOL);
    $this->assertTrue($bl->nullable);
    $this->assertFalse($bl->increment);
    $this->assertFalse($bl->primary);
    $this->assertFalse($bl->default);

    $this->assertEquals($dt->type, Sabel_DB_Type::DATETIME);
    $this->assertTrue($dt->nullable);
    $this->assertFalse($dt->increment);
    $this->assertFalse($dt->primary);

    $this->assertEquals($ft->type, Sabel_DB_Type::FLOAT);
    $this->assertEquals($ft->max,  3.4028235E38);
    $this->assertEquals($ft->min, -3.4028235E38);
    $this->assertTrue($ft->nullable);
    $this->assertFalse($ft->increment);
    $this->assertFalse($ft->primary);
    $this->assertEquals($ft->default, (float)1);

    $this->assertEquals($db->type, Sabel_DB_Type::DOUBLE);
    $this->assertEquals($db->max,  1.79769E308);
    $this->assertEquals($db->min, -1.79769E308);
    $this->assertFalse($db->nullable);
    $this->assertFalse($db->increment);
    $this->assertFalse($db->primary);

    $this->assertEquals($tx->type, Sabel_DB_Type::TEXT);
    $this->assertTrue($tx->nullable);
    $this->assertFalse($tx->increment);
    $this->assertFalse($tx->primary);

    if (self::$db !== "SQLITE") {
      $this->assertFalse($schema->isForeignKey("name"));
      $this->assertTrue($schema->isForeignKey("users_id"));
      $this->assertTrue($schema->isForeignKey("city_id"));

      $fkeys = $schema->getForeignKeys();
      $this->assertEquals($fkeys["users_id"]["referenced_table"], "users");
      $this->assertEquals($fkeys["users_id"]["referenced_column"], "id");
      $this->assertEquals($fkeys["users_id"]["on_delete"], "CASCADE");
      $this->assertEquals($fkeys["users_id"]["on_update"], "NO ACTION");

      $this->assertEquals($fkeys["city_id"]["referenced_table"], "city");
      $this->assertEquals($fkeys["city_id"]["referenced_column"], "id");
      $this->assertEquals($fkeys["city_id"]["on_delete"], "NO ACTION");

      // @todo
      if (self::$db === "MYSQL") {
        $results = $model->executeQuery("SELECT VERSION() as version");
        $exp = explode(".", $results[0]->version);
        if ($exp[1] === "1") {
          $this->assertEquals($fkeys["city_id"]["on_update"], "RESTRICT");
        } else {
          $this->assertEquals($fkeys["city_id"]["on_update"], "NO ACTION");
        }
      } elseif (self::$db === "IBASE") {
        $this->assertEquals($fkeys["city_id"]["on_update"], "RESTRICT");
      } else {
        $this->assertEquals($fkeys["city_id"]["on_update"], "NO ACTION");
      }
    }

    $this->assertFalse($schema->isUnique("name"));
    $this->assertTrue($schema->isUnique("uni1"));
    $this->assertTrue($schema->isUnique("uni2"));
    $this->assertTrue($schema->isUnique("uni3"));
  }

  public function testBridge()
  {
    $model = MODEL('Student');
    $model->insert(array('id' => 1, 'name' => 'suzuki'));
    $model->insert(array('id' => 2, 'name' => 'satou'));
    $model->insert(array('id' => 3, 'name' => 'tanaka'));
    $model->insert(array('id' => 4, 'name' => 'koike'));
    $model->insert(array('id' => 5, 'name' => 'yamada'));

    $model = MODEL('Course');
    $model->insert(array('id' => 1, 'course_name' => 'math'));
    $model->insert(array('id' => 2, 'course_name' => 'physics'));
    $model->insert(array('id' => 3, 'course_name' => 'sience'));

    $model = MODEL('StudentCourse');
    $model->insert(array('student_id' => 1, 'course_id' => 1));
    $model->insert(array('student_id' => 1, 'course_id' => 2));
    $model->insert(array('student_id' => 2, 'course_id' => 2));
    $model->insert(array('student_id' => 3, 'course_id' => 1));
    $model->insert(array('student_id' => 4, 'course_id' => 3));
    $model->insert(array('student_id' => 2, 'course_id' => 3));
    $model->insert(array('student_id' => 3, 'course_id' => 2));

    $pkey = $model->getSchema()->getPrimaryKey();
    $this->assertTrue(is_array($pkey));

    $suzuki = MODEL('Student')->selectOne(1);
    $this->assertEquals($suzuki->name, 'suzuki');

    $bridge = new Sabel_DB_Model_Bridge($suzuki, "StudentCourse");
    $courses = $bridge->getChild('Course');
    $this->assertEquals(count($courses), 2);

    $yamada = MODEL('Student')->selectOne(5);
    $this->assertEquals($yamada->name, 'yamada');

    $bridge = new Sabel_DB_Model_Bridge($yamada, "StudentCourse");
    $courses = $bridge->getChild('Course');
    $this->assertFalse($courses);

    $course = MODEL('Course')->selectOne(1);
    $this->assertEquals($course->course_name, 'math');

    $bridge = new Sabel_DB_Model_Bridge($course, "StudentCourse");
    $students = $bridge->getChild('Student');
    $this->assertEquals(count($students), 2);
  }

  public function testTimer()
  {
    $model = MODEL('Timer');
    $model->insert(array('id' => 1));

    $model = MODEL('Timer')->selectOne(1);
    $this->assertEquals($model->id, 1);
    $this->assertNotNull($model->auto_update);
    $this->assertNotNull($model->auto_create);

    $model->auto_create = date("Y-m-d H:i:s");
    $model->save();

    $model = MODEL('Timer')->selectOne(1);
    $this->assertEquals($model->id, 1);
    $this->assertNotNull($model->auto_update);
    $this->assertNotNull($model->auto_create);
  }

  public function testCustomCommand()
  {
    $model = MODEL('Timer');
    $result = $model->custom("test");

    $this->assertEquals($result, "test");
  }

  public function testClear()
  {
    Sabel_DB_Schema_Loader::clear();
    Sabel_DB_Connection::closeAll();
  }
}

class Proxy extends Sabel_DB_Model
{
  public function __construct($mdlName)
  {
    $this->initialize($mdlName);
  }
}

class Users extends Sabel_DB_Model
{
  protected $parents = array('City');
}

class City extends Sabel_DB_Model
{
  protected $parents = array('Country');
}

class Country extends Sabel_DB_Model
{
  protected $children = array('City');
}

class Timer extends Sabel_DB_Model
{

}

class Customer extends Sabel_DB_Model
{
  protected $connectionName = 'default2';
}

class Schema_TestCondition
{
  public function get()
  {
    $cols = array();

    $cols['id']       = array('type' => Sabel_DB_Type::INT, 'max' => 2147483647, 'min' => -2147483648,
                              'increment' => true, 'nullable' => false, 'primary' => true,
                              'default' => null);
    $cols['status']   = array('type' => Sabel_DB_Type::BOOL, 'increment' => false, 'nullable' => true,
                              'primary' => false, 'default' => null);
    $cols['registed'] = array('type' => Sabel_DB_Type::DATETIME, 'increment' => false, 'nullable' => true,
                              'primary' => false, 'default' => null);
    $cols['point']    = array('type' => Sabel_DB_Type::INT, 'max' => 2147483647, 'min' => -2147483648,
                              'increment' => false, 'nullable' => true, 'primary' => false,
                              'default' => null);
    return $cols;
  }

  public function getProperty()
  {
    $property['tableEngine'] = 'MyISAM';
    $property['fkeys']   = null;
    $property['uniques'] = null;

    return $property;
  }
}

class Schema_Customer
{
  public function get()
  {
    $cols = array();

    $cols['id']   = array('type' => Sabel_DB_Type::INT, 'max' => 2147483647, 'min' => -2147483648,
                          'increment' => false, 'nullable' => false, 'primary' => true,
                          'default' => null);
    $cols['name'] = array('type' => Sabel_DB_Type::STRING, 'max' => 24, 'increment' => false,
                          'nullable' => true, 'primary' => false, 'default' => null);

    return $cols;
  }

  public function getProperty()
  {
    $property['tableEngine'] = 'InnoDB';
    $property['fkeys']   = null;
    $property['uniques'] = null;

    return $property;
  }
}

class Schema_CustomerOrder
{
  public function get()
  {
    $cols = array();

    $cols['id'] = array('type' => '_INT', 'max' => 2147483647, 'min' => -2147483648,
                        'increment' => true, 'nullable' => false, 'primary' => true,
                        'default' => null);
    $cols['customer_id'] = array('type' => '_INT', 'max' => 2147483647, 'min' => -2147483648,
                                 'increment' => false, 'nullable' => true, 'primary' => false,
                                 'default' => null);
    $cols['buy_date'] = array('type' => '_DATETIME', 'increment' => false, 'nullable' => true,
                              'primary' => false, 'default' => null);
    $cols['amount']   = array('type' => '_INT', 'max' => 2147483647, 'min' => -2147483648,
                              'increment' => false, 'nullable' => true, 'primary' => false,
                              'default' => null);
    return $cols;
  }

  public function getProperty()
  {
    $property['tableEngine'] = 'InnoDB';
    $property['fkeys']   = null;
    $property['uniques'] = null;

    return $property;
  }
}

class CustomerCascadeDelete
{
  public function getChain()
  {
    return array("Customer" => array("CustomerOrder"));
  }

  public function getKeys()
  {
    $keys = array();
    $keys["Customer"]["CustomerOrder"] = array("id" => "id", "fkey" => "customer_id");

    return $keys;
  }
}

class CountryCascadeDelete
{
  public function getChain()
  {
    $chains = array();

    $chains["Country"] = array("City");
    $chains["City"]    = array("Company", "Users");
    $chains["Company"] = array("Users");
    $chains["Users"]   = array("Blog");

    return $chains;
  }

  public function getKeys()
  {
    $keys = array();
    $keys["Country"]["City"]  = array("id" => "id", "fkey" => "country_id");
    $keys["City"]["Company"]  = array("id" => "id", "fkey" => "city_id");
    $keys["City"]["Users"]    = array("id" => "id", "fkey" => "city_id");
    $keys["Company"]["Users"] = array("id" => "id", "fkey" => "company_id");
    $keys["Users"]["Blog"]    = array("id" => "id", "fkey" => "users_id");

    return $keys;
  }
}

class TimeRecorder
{
  const UPDATE_COLUMN = "auto_update";
  const INSERT_COLUMN = "auto_create";

  public function execute($command)
  {
    $model   = $command->getModel();
    $values  = $model->getSaveValues();
    $columns = $model->getColumnNames();

    if (!$model->isSelected()) {
      if (in_array(self::INSERT_COLUMN, $columns)) {
        $val = $model->{self::INSERT_COLUMN};
        if ($val === null) $values[self::INSERT_COLUMN] = now();
      }
    }

    if (in_array(self::UPDATE_COLUMN, $columns)) {
      $val = $model->{self::UPDATE_COLUMN};
      if ($val === null) $values[self::UPDATE_COLUMN] = now();
    }

    $model->setSaveValues($values);
  }
}

Sabel_DB_Command_Before::regist("TimeRecorder",
                                Sabel_DB_Command::UPDATE|Sabel_DB_Command::INSERT,
                                array("model" => array("include" => array("Timer"))));

class Sabel_DB_Command_Custom extends Sabel_DB_Command_Base
{
  protected $command = Sabel_DB_Command::CUSTOM;

  protected function run($executer)
  {
    $args = $executer->getArguments();
    $executer->setResult($args[0]);
  }
}

interface Sabel_DB_Command
{
  const SELECT = 0x01;
  const INSERT = 0x02;
  const UPDATE = 0x04;
  const DELETE = 0x08;
  const QUERY  = 0x10;
  const JOIN   = 0x20;

  const ARRAY_INSERT = 0x40;

  const CUSTOM = 0x80;
}

