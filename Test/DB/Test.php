<?php

class Test_DB_Test extends SabelTestCase
{
  public static $db = '';
  public static $TABLES = array('basic', 'users', 'city', 'country',
                                'test_for_like', 'test_condition', 'blog',
                                'customer_order', 'classification', 'favorite_item');


  public function testBasic()
  {
    $basic = Sabel_DB_Model::load('Basic');
    $basic->id   = 1;
    $basic->name = 'basic name1';
    $basic->save();

    $basic = Sabel_DB_Model::load('Basic');
    $basic->save(array('id' => 2, 'name' => 'basic name2'));

    $basic = Sabel_DB_Model::load('Basic');
    $model = $basic->selectOne(1);
    $this->assertEquals($model->name, 'basic name1');

    $model = Sabel_DB_Model::load('Basic')->selectOne('name', 'basic name2');
    $this->assertEquals((int)$model->id, 2);
  }

  public function testParent()
  {
    $data = array();
    $data[] = array('id' => 1, 'name' => 'username1', 'email' => 'user1@example.com', 'city_id' => 4);
    $data[] = array('id' => 2, 'name' => 'username2', 'email' => 'user2@example.com', 'city_id' => 3);
    $data[] = array('id' => 3, 'name' => 'username3', 'email' => 'user3@example.com', 'city_id' => 2);
    $data[] = array('id' => 4, 'name' => 'username4', 'email' => 'user4@example.com', 'city_id' => 1);
    $users = new Users();
    $users->multipleInsert($data);

    $data = array();
    $data[] = array('id' => 1, 'name' => 'tokyo',     'classification_id' => 1, 'country_id' => 1);
    $data[] = array('id' => 2, 'name' => 'osaka',     'classification_id' => 2, 'country_id' => 1);
    $data[] = array('id' => 3, 'name' => 'san diego', 'classification_id' => 2, 'country_id' => 2);
    $data[] = array('id' => 4, 'name' => 'rondon',    'classification_id' => 1, 'country_id' => 3);

    $city = Sabel_DB_Model::load('City');
    $city->multipleInsert($data);

    $city = Sabel_DB_Model::load('Classification');
    $city->save(array('id' => 1, 'class_name' => 'classname1'));
    $city->save(array('id' => 2, 'class_name' => 'classname2'));

    $data = array();
    $data[] = array('id' => 1, 'name' => 'japan');
    $data[] = array('id' => 2, 'name' => 'usa');
    $data[] = array('id' => 3, 'name' => 'england');

    $country = new Country();
    $country->multipleInsert($data);

    $model = new Users();
    $model->setConstraint('order', 'id');
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

  public function testChild()
  {
    $model = new Country();
    $model->sconst('order', 'id desc');
    $countries = $model->select();

    $country3 = $countries[0];
    $country2 = $countries[1];
    $country1 = $countries[2];

    $this->assertEquals($country3->name, 'england');
    $this->assertEquals($country2->name, 'usa');

    $cities = $country1->City;
    $city1  = $cities[0];
    $city2  = $cities[1];

    $this->assertEquals($city1->name, 'tokyo');
    $this->assertEquals($city2->name, 'osaka');
  }

  public function testChildCondition()
  {
    $model = new Country();
    $model->ccond('name', 'osaka');
    $japan = $model->selectOne('name', 'japan');

    $this->assertEquals(count($japan->City), 1);
    $this->assertEquals($japan->City[0]->name, 'osaka');

    $model = new Country();
    $japan = $model->selectOne('name', 'japan');
    $this->assertEquals(count($japan->City), 2);
  }

  public function clearChild()
  {
    $model  = new City();
    $cities = $model->select();
    $this->assertEquals(count($cities), 4);

    $model = new Country();
    $japan = $model->selectOne('name', 'japan');
    $japan->clearChild('City');

    $model  = new City();
    $cities = $model->select();
    $this->assertEquals(count($cities), 2);
  }

  public function newChild()
  {
    $model = new Country();
    $japan = $model->selectOne('name', 'japan');
    $city  = $japan->newChild('City');

    $city->id   = 1;
    $city->name = 'nagoya';
    $city->save();

    $city  = $japan->newChild('City');

    $city->id   = 2;
    $city->name = 'hiroshima';
    $city->save();

    $city = new City(1);
    $this->assertEquals($city->name, 'nagorya');
    $this->assertEquals((int)$city->country_id, 1);

    $city = new City(2);
    $this->assertEquals($city->name, 'hiroshima');
    $this->assertEquals((int)$city->country_id, 1);
  }

  public function testLike()
  {
    $model = Sabel_DB_Model::load('TestForLike');
    $model->save(array('string' => 'aaa'));
    $model->save(array('string' => 'aa_'));
    $model->save(array('string' => 'aba'));
    $newModel = $model->save(array('string' => 'a%a'));
    $this->assertTrue(is_numeric($newModel->id));
    $this->assertEquals($newModel->string, 'a%a');

    $model = Sabel_DB_Model::load('TestForLike');
    $model->setCondition('LIKE_string', 'aa_');
    $result = $model->select();

    $this->assertTrue(is_array($result));
    $this->assertEquals(count($result), 1);
    $this->assertEquals($result[0]->string, 'aa_');

    $model = Sabel_DB_Model::load('TestForLike');
    $model->setCondition('LIKE_string', array('aa_', false));
    $result = $model->select();

    $this->assertEquals(count($result), 2);
    $this->assertEquals($result[0]->string, 'aaa');
    $this->assertEquals($result[1]->string, 'aa_');

    $model = Sabel_DB_Model::load('TestForLike');
    $model->scond('LIKE_string', 'a%a');
    $result = $model->select();

    $this->assertEquals(count($result), 1);
    $this->assertEquals($result[0]->string, 'a%a');

    $model = Sabel_DB_Model::load('TestForLike');
    $model->scond('LIKE_string', array('a%a', false));
    $result = $model->select();

    $this->assertEquals(count($result), 3);
    $this->assertEquals($result[0]->string, 'aaa');
    $this->assertEquals($result[1]->string, 'aba');
    $this->assertEquals($result[2]->string, 'a%a');
  }

  public function testCondition()
  {
    $model = Sabel_DB_Model::load('TestCondition');
    $model->save(array('status' => true,  'registed' => '2005-10-01 10:10:10', 'point' => 1000));
    $model->save(array('status' => false, 'registed' => '2005-09-01 10:10:10', 'point' => 2000));
    $model->save(array('status' => false, 'registed' => '2005-08-01 10:10:10', 'point' => 3000));
    $model->save(array('status' => true,  'registed' => '2005-07-01 10:10:10', 'point' => 4000));
    $model->save(array('status' => false, 'registed' => '2005-06-01 10:10:10', 'point' => 5000));
    $model->save(array('status' => false, 'registed' => '2005-05-01 10:10:10', 'point' => 6000));
    $model->save(array('status' => true,  'registed' => '2005-04-01 10:10:10', 'point' => 7000));
    $model->save(array('status' => false, 'registed' => '2005-03-01 10:10:10', 'point' => 8000));
    $model->save(array('status' => false, 'registed' => '2005-02-01 10:10:10', 'point' => 9000));
    $model->save(array('status' => true,  'registed' => '2005-01-01 10:10:10', 'point' => 10000));

    $model = Sabel_DB_Model::load('TestCondition');
    $model->scond('COMP_point', array('>=', 8000));
    $models = $model->select();
    $this->assertEquals(count($models), 3);

    $models = Sabel_DB_Model::load('TestCondition')->select('COMP_point', array('>=', 8000));
    $this->assertEquals(count($models), 3);

    $models = Sabel_DB_Model::load('TestCondition')->select('COMP_point', array('>', 8000));
    $this->assertEquals(count($models), 2);

    $model = Sabel_DB_Model::load('TestCondition');
    $conditions = array();
    $conditions[] = new Sabel_DB_Condition('COMP_point', array('>=', 8000));
    $conditions[] = new Sabel_DB_Condition('COMP_point', array('<=', 3000));
    $model->setCondition($conditions);
    $models = $model->select();
    $this->assertEquals(count($models), 6);

    $model = Sabel_DB_Model::load('TestCondition');
    $conditions = array();
    $conditions[] = new Sabel_DB_Condition('COMP_point', array('>=', 8000));
    $conditions[] = new Sabel_DB_Condition('COMP_registed', array('>', '2005-08-01 01:01:01'));
    $model->setCondition($conditions);
    $model->sconst('order', 'id');
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

    $model = Sabel_DB_Model::load('TestCondition');
    $model->scond('status', false);
    $models = $model->select();
    $this->assertEquals(count($models), 6);

    $model1 = $models[0];
    $model2 = $models[1];
    $model3 = $models[2];

    $this->assertEquals($model1->point, 2000);
    $this->assertEquals($model2->point, 3000);
    $this->assertEquals($model3->point, 5000);

    $model->unsetCondition();
    $model->scond('status', false, Sabel_DB_Condition::NOT);
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

    $model = Sabel_DB_Model::load('TestCondition');
    $model->scond('BET_registed', array('2005-01-01 11:11:11', '2005-05-05 11:11:11'));
    $model->sconst('order', 'registed');
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

    $model->unsetCondition();

    $condition = new Sabel_DB_Condition('BET_registed',
                                        array('2005-01-01 11:11:11', '2005-05-05 11:11:11'),
                                        Sabel_DB_Condition::NOT);
    $model->setCondition($condition);
    $model->sconst('order', 'registed');
    $models = $model->select();
    $this->assertEquals(count($models), 6);

    $model1 = $models[0];
    $model2 = $models[1];

    $this->assertEquals($model1->point, 10000);
    $this->assertEquals($model2->point, 5000);

    $model = Sabel_DB_Model::load('TestCondition');
    $model->save(array('status' => false, 'registed' => '2004-12-01 10:10:10'));
    $model->save(array('status' => false, 'registed' => '2004-11-01 10:10:10'));
    $model->save(array('status' => true,  'registed' => '2004-10-01 10:10:10', 'point' => 13000));

    $model->scond('point', Sabel_DB_Condition::ISNULL);
    $models = $model->select();
    $this->assertEquals(count($models), 2);

    $model1 = $models[0];
    $model2 = $models[1];

    $this->assertEquals($model1->registed, '2004-12-01 10:10:10');
    $this->assertEquals($model2->registed, '2004-11-01 10:10:10');

    $model->unsetCondition();

    $models = $model->select('point', Sabel_DB_Condition::NOTNULL);
    $this->assertEquals(count($models), 11);

    $model = Sabel_DB_Model::load('TestCondition');
    $model->scond('point', Sabel_DB_Condition::NOTNULL);
    $model->scond('COMP_registed', array('<=', '2005-02-01 10:10:10'));
    $models = $model->select();
    $this->assertEquals(count($models), 3);

    $model1 = $models[0];
    $model2 = $models[1];
    $model3 = $models[2];

    $this->assertEquals($model1->registed, '2005-02-01 10:10:10');
    $this->assertEquals($model2->registed, '2005-01-01 10:10:10');
    $this->assertEquals($model3->registed, '2004-10-01 10:10:10');
  }

  public function testChildConstraint()
  {
    $blog = Sabel_DB_Model::load('Blog');
    $blog->save(array('id' => 1,  'title' => 'title1',  'article' => 'article1',
                      'write_date' => '2005-01-01 01:01:01', 'users_id' => 1));
    $blog->save(array('id' => 2,  'title' => 'title2',  'article' => 'article2',
                      'write_date' => '2005-01-01 02:01:01', 'users_id' => 1));
    $blog->save(array('id' => 3,  'title' => 'title3',  'article' => 'article3',
                      'write_date' => '2005-01-01 03:01:01', 'users_id' => 1));
    $blog->save(array('id' => 4,  'title' => 'title4',  'article' => 'article4',
                      'write_date' => '2005-01-01 04:01:01', 'users_id' => 1));
    $blog->save(array('id' => 5,  'title' => 'title5',  'article' => 'article5',
                      'write_date' => '2005-01-01 05:01:01', 'users_id' => 2));
    $blog->save(array('id' => 6,  'title' => 'title6',  'article' => 'article6',
                      'write_date' => '2005-01-01 06:01:01', 'users_id' => 2));
    $blog->save(array('id' => 7,  'title' => 'title7',  'article' => 'article7',
                      'write_date' => '2005-01-01 07:01:01', 'users_id' => 2));

    $favorite = Sabel_DB_Model::load('FavoriteItem');
    $favorite->save(array('id' => 1, 'name' => 'farorite1',
                          'registed' => '2005-12-01 01:01:01', 'users_id' => 1));
    $favorite->save(array('id' => 2, 'name' => 'farorite2',
                          'registed' => '2005-12-02 01:01:01', 'users_id' => 2));
    $favorite->save(array('id' => 3, 'name' => 'farorite3',
                          'registed' => '2005-12-03 01:01:01', 'users_id' => 2));
    $favorite->save(array('id' => 4, 'name' => 'farorite4',
                          'registed' => '2005-12-04 01:01:01', 'users_id' => 3));
    $favorite->save(array('id' => 5, 'name' => 'farorite5',
                          'registed' => '2005-12-05 01:01:01', 'users_id' => 3));
    $favorite->save(array('id' => 6, 'name' => 'farorite6',
                          'registed' => '2005-12-06 01:01:01', 'users_id' => 1));
    $favorite->save(array('id' => 7, 'name' => 'farorite7',
                          'registed' => '2005-12-07 01:01:01', 'users_id' => 4));

    $user  = new Users(1);
    $blogs = $user->getChild('Blog');
    $this->assertEquals(count($blogs), 4);

    $blog1 = $blogs[0];
    $blog2 = $blogs[1];
    $blog3 = $blogs[2];
    $blog4 = $blogs[3];

    $this->assertEquals($blog1->write_date, '2005-01-01 04:01:01');
    $this->assertEquals($blog2->write_date, '2005-01-01 03:01:01');
    $this->assertEquals($blog3->write_date, '2005-01-01 02:01:01');
    $this->assertEquals($blog4->write_date, '2005-01-01 01:01:01');

    $user  = new Users(1);
    $user->cconst('order', 'write_date');
    $blogs = $user->getChild('Blog');
    $this->assertEquals(count($blogs), 4);

    $blog1 = $blogs[0];
    $blog2 = $blogs[1];
    $blog3 = $blogs[2];
    $blog4 = $blogs[3];

    $this->assertEquals($blog1->write_date, '2005-01-01 01:01:01');
    $this->assertEquals($blog2->write_date, '2005-01-01 02:01:01');
    $this->assertEquals($blog3->write_date, '2005-01-01 03:01:01');
    $this->assertEquals($blog4->write_date, '2005-01-01 04:01:01');

    $user  = new Users(2);
    $user->cconst('FavoriteItem', array('registed', 'registed desc'));
    $blogs = $user->getChild('Blog');
    $items = $user->getChild('FavoriteItem');

    $this->assertEquals(count($blogs), 3);
    $this->assertEquals(count($items), 2);

    $blog1 = $blogs[0];
    $blog2 = $blogs[1];
    $blog3 = $blogs[2];

    $this->assertEquals($blog1->write_date, '2005-01-01 07:01:01');
    $this->assertEquals($blog2->write_date, '2005-01-01 06:01:01');
    $this->assertEquals($blog3->write_date, '2005-01-01 05:01:01');

    $item1 = $items[0];
    $item2 = $items[1];

    $this->assertEquals($item1->registed, '2005-12-02 01:01:01');
    $this->assertEquals($item2->registed, '2005-12-03 01:01:01');
  }

  public function testChildPaginate()
  {
    $user  = new Users(1);
    $user->cconst(array('order' => 'write_date desc', 'limit' => 2));
    $blogs = $user->getChild('Blog');
    $this->assertEquals(count($blogs), 2);

    $blog1 = $blogs[0];
    $blog2 = $blogs[1];

    $this->assertEquals($blog1->write_date, '2005-01-01 04:01:01');
    $this->assertEquals($blog2->write_date, '2005-01-01 03:01:01');

    $user  = new Users(1);
    $user->cconst(array('order' => 'write_date desc', 'limit' => 2, 'offset' => 2));
    $blogs = $user->getChild('Blog');
    $this->assertEquals(count($blogs), 2);

    $blog1 = $blogs[0];
    $blog2 = $blogs[1];

    $this->assertEquals($blog1->write_date, '2005-01-01 02:01:01');
    $this->assertEquals($blog2->write_date, '2005-01-01 01:01:01');
  }

  public function testAggregate()
  {
    $model = Sabel_DB_Model::load('Customer');
    $model->execute('DELETE FROM customer');

    $model->save(array('id' => 1, 'name' => 'name1'));
    $model->save(array('id' => 2, 'name' => 'name2'));

    $order = Sabel_DB_Model::load('CustomerOrder');
    $order->save(array('customer_id' => 1, 'buy_date' => '2005-01-01 10:10:10', 'amount' => 1000));
    $order->save(array('customer_id' => 1, 'buy_date' => '2005-02-01 10:10:10', 'amount' => 2000));
    $order->save(array('customer_id' => 1, 'buy_date' => '2005-03-01 10:10:10', 'amount' => 3000));
    $order->save(array('customer_id' => 1, 'buy_date' => '2005-04-01 10:10:10', 'amount' => 4000));
    $order->save(array('customer_id' => 1, 'buy_date' => '2005-05-01 10:10:10', 'amount' => 5000));
    $order->save(array('customer_id' => 2, 'buy_date' => '2005-06-01 10:10:10', 'amount' => 6000));
    $order->save(array('customer_id' => 2, 'buy_date' => '2005-07-01 10:10:10', 'amount' => 7000));
    $order->save(array('customer_id' => 2, 'buy_date' => '2005-08-01 10:10:10', 'amount' => 8000));

    $customer = Sabel_DB_Model::load('Customer')->selectOne(1);
    $customer->setConstraint('order', 'customer_id');
    $func   = 'sum(amount) as sum, avg(amount) as avg';
    $result = $customer->aggregate($func, 'CustomerOrder', 'customer_id');
    $this->assertEquals(count($result), 2);

    $res1 = $result[0];
    $res2 = $result[1];

    $this->assertEquals((int)$res1->sum, 15000);
    $this->assertEquals((int)$res1->avg, 3000);
    $this->assertEquals((int)$res2->sum, 21000);
    $this->assertEquals((int)$res2->avg, 7000);

    $model  = Sabel_DB_Model::load('CustomerOrder');
    $model->setConstraint('order', 'customer_id');
    $func   = 'sum(amount) as sum, avg(amount) as avg';
    $result = $model->aggregate($func, null, 'customer_id');
    $this->assertEquals(count($result), 2);

    $res1 = $result[0];
    $res2 = $result[1];

    $this->assertEquals((int)$res1->sum, 15000);
    $this->assertEquals((int)$res1->avg, 3000);
    $this->assertEquals((int)$res2->sum, 21000);
    $this->assertEquals((int)$res2->avg, 7000);
  }

  public function testDBRelation()
  {
    $customer = Sabel_DB_Model::load('Customer')->selectOne(2);
    $orders   = $customer->getChild('CustomerOrder');
    $this->assertEquals(count($orders), 3);

    $order1 = $orders[0];
    $order2 = $orders[1];
    $order3 = $orders[2];

    $this->assertEquals($order1->amount, 6000);
    $this->assertEquals($order2->amount, 7000);
    $this->assertEquals($order3->amount, 8000);

    $model = Sabel_DB_Model::load('CustomerOrder');
    $model->enableParent();
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

    $customer = Sabel_DB_Model::load('Customer')->selectOne(2);
    $order    = $customer->newChild('CustomerOrder');

    $order->buy_date = '2005-08-01 10:10:10';
    $order->amount   = 9000;
    $order->save();

    $orders = Sabel_DB_Model::load('Customer')->selectOne(2)->getChild('CustomerOrder');
    $this->assertEquals(count($orders), 4);
  }

  public function testJoin()
  {
    $model = new Users();
    $model->sconst('order', 'users.id');
    $modelPairs = array('Users:City', 'City:Country');
    $users = $model->selectJoin($modelPairs);

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
    $model = new Users();
    $model->sconst('order', 'users.id');
    $modelPairs = array('Users:City', 'City:Country', 'City:Classification');
    $users = $model->selectJoin($modelPairs);

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
  }

  public function testFusionModel()
  {
    $model = Sabel_DB_Model::fusion(array('Users', 'City', 'Country'));
    $model->setCombination(array('Users:City', 'City:Country'));
    $fusioned = $model->selectOne('id', 4);

    $this->assertEquals((int)$fusioned->id, 4);
    $this->assertEquals((int)$fusioned->City_id, 1);
    $this->assertEquals((int)$fusioned->Country_id, 1);
    $this->assertEquals($fusioned->name, 'username4');
    $this->assertEquals($fusioned->email, 'user4@example.com');
    $this->assertEquals($fusioned->City_name, 'tokyo');
    $this->assertEquals($fusioned->Country_name, 'japan');
  }

  public function testFusionCondition()
  {
    $model = Sabel_DB_Model::fusion(array('Users', 'City', 'Country'));
    $model->setCombination(array('City:Country', 'Users:City'));
    $fusioned = $model->selectOne('id', 4);

    $this->assertEquals((int)$fusioned->id, 4);
    $this->assertEquals((int)$fusioned->city_id, 1);
    $this->assertEquals((int)$fusioned->City_id, 1);
    $this->assertEquals((int)$fusioned->Country_id, 1);
    $this->assertEquals($fusioned->name, 'username4');
    $this->assertEquals($fusioned->email, 'user4@example.com');
    $this->assertEquals($fusioned->City_name, 'tokyo');
    $this->assertEquals($fusioned->Country_name, 'japan');

    $model = Sabel_DB_Model::fusion(array('Users', 'City', 'Country'));
    $model->setCombination(array('City.id:Users.city_id', 'Country.id:City.country_id'));
    $fusioned = $model->selectOne('id', 4);

    $this->assertEquals((int)$fusioned->id, 4);
    $this->assertEquals((int)$fusioned->City_id, 1);
    $this->assertEquals((int)$fusioned->Country_id, 1);
    $this->assertEquals($fusioned->name, 'username4');
    $this->assertEquals($fusioned->email, 'user4@example.com');
    $this->assertEquals($fusioned->City_name, 'tokyo');
    $this->assertEquals($fusioned->Country_name, 'japan');
  }

  public function testMoreFusion()
  {
    $model = Sabel_DB_Model::fusion(array('Users', 'City', 'Classification', 'Country'));
    $model->setCombination(array('Users:City', 'City:Classification', 'City:Country'));
    $fusioned = $model->selectOne('id', 4);

    $this->assertEquals((int)$fusioned->id, 4);
    $this->assertEquals((int)$fusioned->city_id, 1);
    $this->assertEquals((int)$fusioned->City_id, 1);
    $this->assertEquals((int)$fusioned->Country_id, 1);
    $this->assertEquals((int)$fusioned->Classification_id, 1);
    $this->assertEquals($fusioned->name, 'username4');
    $this->assertEquals($fusioned->email, 'user4@example.com');
    $this->assertEquals($fusioned->City_name, 'tokyo');
    $this->assertEquals($fusioned->Country_name, 'japan');
    $this->assertEquals($fusioned->Classification_class_name, 'classname1');
    $this->assertEquals($fusioned->class_name, 'classname1');
  }

  public function testMoreFusionConditionTest()
  {
    $model = Sabel_DB_Model::fusion(array('City', 'Users', 'Classification', 'Country'));
    $model->setCombination(array('Users:City', 'City:Classification', 'City:Country'));
    $fusioned = $model->selectOne('id', 4);

    $this->assertEquals((int)$fusioned->Users_id, 1);
    $this->assertEquals((int)$fusioned->Users_city_id, 4);
    $this->assertEquals((int)$fusioned->id, 4);
    $this->assertEquals((int)$fusioned->Country_id, 3);
    $this->assertEquals((int)$fusioned->Classification_id, 1);
    $this->assertEquals($fusioned->Users_name, 'username1');
    $this->assertEquals($fusioned->email, 'user1@example.com');
    $this->assertEquals($fusioned->name, 'rondon');
    $this->assertEquals($fusioned->Country_name, 'england');
    $this->assertEquals($fusioned->Classification_class_name, 'classname1');
    $this->assertEquals($fusioned->class_name, 'classname1');
  }

  public function testUpdateFusionModel()
  {
    $model = Sabel_DB_Model::fusion(array('Users', 'City', 'Classification', 'Country'));
    $model->setCombination(array('Users:City', 'City:Classification', 'City:Country'));
    $fusioned = $model->selectOne('id', 4);

    $fusioned->city_id = 2;
    $fusioned->save();

    $model = Sabel_DB_Model::fusion(array('Users', 'City', 'Classification', 'Country'));
    $model->setCombination(array('Users:City', 'City:Classification', 'City:Country'));
    $fusioned = $model->selectOne('id', 4);

    $this->assertEquals((int)$fusioned->id, 4);
    $this->assertEquals((int)$fusioned->city_id, 2);
    $this->assertEquals((int)$fusioned->City_id, 2);
    $this->assertEquals($fusioned->City_name, 'osaka');

    $model = Sabel_DB_Model::fusion(array('Users', 'City', 'Classification', 'Country'));
    $model->setCombination(array('Users:City', 'City:Classification', 'City:Country'));
    $fusioned = $model->selectOne('id', 4);

    $fusioned->City_name = 'Osaka';
    $fusioned->save();

    $model = Sabel_DB_Model::fusion(array('Users', 'City', 'Classification', 'Country'));
    $model->setCombination(array('Users.city_id:City.id',
                                 'City.classification_id:Classification.id',
                                 'City.country_id:Country.id'));

    $fusioned = $model->selectOne('id', 4);

    $this->assertEquals((int)$fusioned->id, 4);
    $this->assertEquals((int)$fusioned->city_id, 2);
    $this->assertEquals((int)$fusioned->City_id, 2);
    $this->assertEquals($fusioned->City_name, 'Osaka');
  }

  public function testRemove()
  {
    $model = Sabel_DB_Model::load('TestCondition');
    $this->assertEquals($model->getCount(), 13);
    $model->unsetCondition();
    $model->remove('point', 1000);

    $model = Sabel_DB_Model::load('TestCondition');
    $this->assertEquals($model->getCount(), 12);
    $model->unsetCondition();

    $model->scond('point', Sabel_DB_Condition::ISNULL);
    $this->assertEquals($model->getCount(), 2);

    Sabel_DB_Model::load('TestCondition')->remove('point', Sabel_DB_Condition::ISNULL);

    $model = Sabel_DB_Model::load('TestCondition');
    $this->assertEquals($model->getCount(), 10);

    $model = Sabel_DB_Model::load('TestCondition');
    $conditions = array();
    $conditions[] = new Sabel_DB_Condition('point', 10000);
    $conditions[] = new Sabel_DB_Condition('COMP_point', array('<=', 4000));
    $model->setCondition($conditions);
    $model->remove();

    $model = Sabel_DB_Model::load('TestCondition');
    $this->assertEquals($model->getCount(), 6);
    $model->unsetCondition();

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
  }

  public function testCascadeDelete()
  {
    $country = new Country();
    @$country->cascadeDelete(1);

    $country   = new Country();
    $countries = $country->select();

    $this->assertEquals(count($countries), 2);
    $this->assertEquals($countries[0]->name, 'usa');
    $this->assertEquals($countries[1]->name, 'england');

    $model  = Sabel_DB_Model::load('City');
    $cities = $model->select();

    $this->assertEquals(count($cities), 2);
    $this->assertEquals($cities[0]->name, 'san diego');
    $this->assertEquals($cities[1]->name, 'rondon');

    $users = new Users();
    $users = $users->select();

    $this->assertEquals(count($users), 2);
    $this->assertEquals($users[0]->name, 'username1');
    $this->assertEquals($users[1]->name, 'username2');
  }

  public function testTransaction()
  {
    Sabel_DB_Model::load('')->execute('DELETE FROM customer_order');
    $model = Sabel_DB_Model::load('CustomerOrder');
    $model->setConnectName('default2');
    $model->execute('DELETE FROM customer');

    $customers = Sabel_DB_Model::load('Customer')->select();
    $this->assertFalse($customers);

    $orders = Sabel_DB_Model::load('CustomerOrder')->select();
    $this->assertFalse($orders);

    $model = Sabel_DB_Model::load('CustomerOrder');
    Sabel_DB_Transaction::add($model);
    $model->save(array('customer_id' => 1, 'buy_date' => '1000-01-01 01:01:01', 'amount' => 1000));
    $model->save(array('customer_id' => 1, 'buy_date' => '1000-01-01 01:01:01', 'amount' => 1000));
    $model->save(array('customer_id' => 1, 'buy_date' => '1000-01-01 01:01:01', 'amount' => 1000));

    $model = Sabel_DB_Model::load('Customer');
    Sabel_DB_Transaction::add($model);
    $model->save(array('id' => 1, 'name' => 'name'));
    $model->save(array('id' => 2, 'name' => 'name'));
    try { @$model->save(array('id' => 3, 'nama' => 'name')); } catch (Exception $e) {}

    Sabel_DB_Transaction::commit();

    $customers = Sabel_DB_Model::load('Customer')->select();
    $this->assertFalse($customers);

    $orders = Sabel_DB_Model::load('CustomerOrder')->select();
    $this->assertFalse($orders);
  }

  public function testUpdate()
  {
    $model = Sabel_DB_Model::load('Customer');
    $model->save(array('id' => 1, 'name' => 'name1'));
    $model->save(array('id' => 2, 'name' => 'name2'));

    $customer = Sabel_DB_Model::load('Customer')->selectOne(1);
    $this->assertEquals($customer->name, 'name1');

    $customer->name = 'new name1';
    $customer = $customer->save();

    $this->assertEquals((int)$customer->id, 1);
    $this->assertEquals($customer->name, 'new name1');

    $customer = Sabel_DB_Model::load('Customer')->selectOne(1);
    $this->assertEquals($customer->name, 'new name1');

    $customer = Sabel_DB_Model::load('Customer')->selectOne(100);
    $this->assertFalse($customer->isSelected());

    $customer->name = 'name100';
    $customer = $customer->save();
    $this->assertEquals((int)$customer->id, 100);
    $this->assertEquals($customer->name, 'name100');

    $customer = Sabel_DB_Model::load('Customer')->selectOne(100);
    $this->assertTrue($customer->isSelected());
    $this->assertEquals($customer->name, 'name100');

    $model = Sabel_DB_Model::load('CustomerOrder');
    $model->scond(5);
    $model->scond('customer_id', 10);
    $order = $model->selectOne();
    $this->assertFalse($order->isSelected());

    $model->buy_date = '1999-01-01 12:34:55';
    $model->amount   = 9999;
    $model->save();

    $order = Sabel_DB_Model::load('CustomerOrder')->selectOne(5);
    $this->assertTrue($order->isSelected());

    $this->assertEquals($order->id, 5);
    $this->assertEquals($order->customer_id, 10);
    $this->assertEquals($order->buy_date, '1999-01-01 12:34:55');
    $this->assertEquals($order->amount, 9999);
  }

  public function testSchema()
  {
    $model  = Sabel_DB_Model::load('SchemaTest');
    $schema = $model->getTableSchema();

    $id = $schema->id;
    $nm = $schema->name;
    $bl = $schema->bl;
    $dt = $schema->dt;
    $ft = $schema->ft_val;
    $db = $schema->db_val;
    $tx = $schema->tx;

    $this->assertEquals($id->type, Sabel_DB_Schema_Const::INT);
    $this->assertEquals($id->max,  2147483647);
    $this->assertEquals($id->min, -2147483648);
    $this->assertFalse($id->nullable);
    $this->assertTrue($id->increment);
    $this->assertTrue($id->primary);

    $this->assertEquals($nm->type, Sabel_DB_Schema_Const::STRING);
    $this->assertEquals($nm->max, 128);
    $this->assertFalse($nm->nullable);
    $this->assertFalse($nm->increment);
    $this->assertFalse($nm->primary);
    $this->assertEquals($nm->default, 'test');

    $this->assertEquals($bl->type, Sabel_DB_Schema_Const::BOOL);
    $this->assertTrue($bl->nullable);
    $this->assertFalse($bl->increment);
    $this->assertFalse($bl->primary);
    $this->assertFalse($bl->default);

    $this->assertEquals($dt->type, Sabel_DB_Schema_Const::DATETIME);
    $this->assertTrue($dt->nullable);
    $this->assertFalse($dt->increment);
    $this->assertFalse($dt->primary);

    $this->assertEquals($ft->type, Sabel_DB_Schema_Const::FLOAT);
    $this->assertEquals($ft->max,  3.4028235E38);
    $this->assertEquals($ft->min, -3.4028235E38);
    $this->assertTrue($ft->nullable);
    $this->assertFalse($ft->increment);
    $this->assertFalse($ft->primary);
    $this->assertEquals($ft->default, 1);

    $this->assertEquals($db->type, Sabel_DB_Schema_Const::DOUBLE);
    $this->assertEquals($db->max,  1.79769E308);
    $this->assertEquals($db->min, -1.79769E308);
    $this->assertFalse($db->nullable);
    $this->assertFalse($db->increment);
    $this->assertFalse($db->primary);

    $this->assertEquals($tx->type, Sabel_DB_Schema_Const::TEXT);
    $this->assertTrue($tx->nullable);
    $this->assertFalse($tx->increment);
    $this->assertFalse($tx->primary);
  }

  public function testClear()
  {
    Sabel_DB_SimpleCache::clear();
    Sabel_DB_Connection::closeAll();
  }
}

class Users extends Sabel_DB_Relation
{
  protected $withParent = true;
  protected $defChildConstraints = array('order' => 'write_date desc');
}

class Country extends Sabel_DB_Relation
{
  protected $myChildren = 'City';
}

class Schema_TestCondition
{
  public function get()
  {
    $cols = array();

    $cols['id']       = array('type' => 'INT', 'max' => 2147483647, 'min' => -2147483648,
                              'increment' => true, 'nullable' => false, 'primary' => true,
                              'default' => null);
    $cols['status']   = array('type' => 'BOOL', 'increment' => false, 'nullable' => true,
                              'primary' => false, 'default' => null);
    $cols['registed'] = array('type' => 'TIMESTAMP', 'increment' => false, 'nullable' => true,
                              'primary' => false, 'default' => null);
    $cols['point']    = array('type' => 'INT', 'max' => 2147483647, 'min' => -2147483648,
                              'increment' => false, 'nullable' => true, 'primary' => false,
                              'default' => null);
    return $cols;
  }

  public function getParents()
  {
    return null;
  }

  public function getProperty()
  {
    $property = array('connectName'  => 'default',
                      'primaryKey'   => 'id',
                      'incrementKey' => 'id',
                      'tableEngine'  => 'MyISAM');

    return $property;
  }
}

class Schema_Customer
{
  public function get()
  {
    $cols = array();

    $cols['id']   = array('type' => 'INT', 'max' => 2147483647, 'min' => -2147483648,
                          'increment' => false, 'nullable' => false, 'primary' => true,
                          'default' => null);
    $cols['name'] = array('type' => 'STRING', 'max' => 24, 'increment' => false,
                          'nullable' => true, 'primary' => false, 'default' => null);

    return $cols;
  }

  public function getParents()
  {
    return null;
  }

  public function getProperty()
  {
    $property = array('connectName'  => 'default2',
                      'primaryKey'   => 'id',
                      'incrementKey' => null,
                      'tableEngine'  => 'MyISAM');

    return $property;
  }
}

class Schema_CustomerOrder
{
  public function get()
  {
    $cols = array();

    $cols['id'] = array('type' => 'INT', 'max' => 2147483647, 'min' => -2147483648,
                        'increment' => true, 'nullable' => false, 'primary' => true,
                        'default' => null);
    $cols['customer_id'] = array('type' => 'INT', 'max' => 2147483647, 'min' => -2147483648,
                                 'increment' => false, 'nullable' => true, 'primary' => false,
                                 'default' => null);
    $cols['buy_date'] = array('type' => 'TIMESTAMP', 'increment' => false, 'nullable' => true,
                              'primary' => false, 'default' => null);
    $cols['amount']   = array('type' => 'INT', 'max' => 2147483647, 'min' => -2147483648,
                              'increment' => false, 'nullable' => true, 'primary' => false,
                              'default' => null);
    return $cols;
  } 

  public function getParents()
  {
    return array('customer');
  }

  public function getProperty()
  {
    $property = array('connectName'  => 'default',
                      'primaryKey'   => 'id',
                      'incrementKey' => 'id',
                      'tableEngine'  => 'MyISAM'); 

    return $property;
  }
}

class Schema_CascadeChain
{
  public static function get()
  {
    $chains = array();

    $chains['default:classification'] = array('default:city');
    $chains['default:city']           = array('default:users');
    $chains['default:users']          = array('default:blog');
    $chains['default:country']        = array('default:city');
    $chains['default2:customer']      = array('default:customer_order');

    return $chains;
  }
}
