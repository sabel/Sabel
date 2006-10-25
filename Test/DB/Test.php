<?php

class Test_DB_Test extends SabelTestCase
{
  public static $TABLES = array('basic', 'users', 'city', 'country',
                                'test_for_like', 'test_condition', 'blog',
                                'customer_order', 'classification');

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
    $model->save(array('string' => 'a%a'));

    $model = Sabel_DB_Model::load('TestForLike');
    $model->LIKE_string('aa_');
    $result = $model->select();

    $this->assertTrue(is_array($result));
    $this->assertEquals(count($result), 1);
    $this->assertEquals($result[0]->string, 'aa_');

    $model = Sabel_DB_Model::load('TestForLike');
    $model->LIKE_string(array('aa_', false));
    $result = $model->select();

    $this->assertEquals(count($result), 2);
    $this->assertEquals($result[0]->string, 'aaa');
    $this->assertEquals($result[1]->string, 'aa_');

    $model = Sabel_DB_Model::load('TestForLike');
    $model->LIKE_string('a%a');
    $result = $model->select();

    $this->assertEquals(count($result), 1);
    $this->assertEquals($result[0]->string, 'a%a');

    $model = Sabel_DB_Model::load('TestForLike');
    $model->LIKE_string(array('a%a', false));
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
    $model->COMP_point(array('>=', 8000));
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
    $model->status(false);
    $models = $model->select();
    $this->assertEquals(count($models), 6);

    $model1 = $models[0];
    $model2 = $models[1];
    $model3 = $models[2];

    $this->assertEquals($model1->point, 2000);
    $this->assertEquals($model2->point, 3000);
    $this->assertEquals($model3->point, 5000);

    $model->unsetCondition();
    $model->status(false, Sabel_DB_Condition::NOT);
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
    $model->BET_registed(array('2005-01-01 11:11:11', '2005-05-05 11:11:11'));
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

    $model->point(Sabel_DB_Condition::ISNULL);
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
    $model->point(Sabel_DB_Condition::NOTNULL);
    $model->COMP_registed(array('<=', '2005-02-01 10:10:10'));
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

  public function testFusionModel()
  {
    $model = Sabel_DB_Model::fusion(array('Users', 'City', 'Country'));
    $model->setFusionCondition(array('Users:City', 'City:Country'));
    $fusioned = $model->selectOne('id', 4);

    $this->assertEquals((int)$fusioned->id, 4);
    $this->assertEquals((int)$fusioned->City_id, 1);
    $this->assertEquals((int)$fusioned->Country_id, 1);
    $this->assertEquals($fusioned->name, 'username4');
    $this->assertEquals($fusioned->email, 'user4@example.com');
    $this->assertEquals($fusioned->City_name, 'tokyo');
    $this->assertEquals($fusioned->Country_name, 'japan');

    $model = Sabel_DB_Model::fusion(array('Users', 'City', 'Country'));
    $model->setFusionCondition(array('City:Country', 'Users:City'));
    $fusioned = $model->selectOne('id', 4);

    $this->assertEquals((int)$fusioned->id, 4);
    $this->assertEquals((int)$fusioned->city_id, 1);
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
    $model->setFusionCondition(array('Users:City', 'City:Classification', 'City:Country'));
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

  public function testUpdateFusionModel()
  {
    $model = Sabel_DB_Model::fusion(array('Users', 'City', 'Classification', 'Country'));
    $model->setFusionCondition(array('Users:City', 'City:Classification', 'City:Country'));
    $fusioned = $model->selectOne('id', 4);

    $fusioned->city_id = 2;
    $fusioned->save();

    $model = Sabel_DB_Model::fusion(array('Users', 'City', 'Classification', 'Country'));
    $model->setFusionCondition(array('Users:City', 'City:Classification', 'City:Country'));
    $fusioned = $model->selectOne('id', 4);

    $this->assertEquals((int)$fusioned->id, 4);
    $this->assertEquals((int)$fusioned->city_id, 2);
    $this->assertEquals((int)$fusioned->City_id, 2);
    $this->assertEquals($fusioned->City_name, 'osaka');

    $model = Sabel_DB_Model::fusion(array('Users', 'City', 'Classification', 'Country'));
    $model->setFusionCondition(array('Users:City', 'City:Classification', 'City:Country'));
    $fusioned = $model->selectOne('id', 4);

    $fusioned->City_name = 'Osaka';
    $fusioned->save();

    $model = Sabel_DB_Model::fusion(array('Users', 'City', 'Classification', 'Country'));
    $model->setFusionCondition(array('Users.city_id:City.id',
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
    $model->remove('point', 1000);

    $model = Sabel_DB_Model::load('TestCondition');
    $this->assertEquals($model->getCount(), 12);

    $model->point(Sabel_DB_Condition::ISNULL);
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
    $chains['default:city']      = array('default:users');
    $chains['default:users']     = array('default:blog');
    $chains['default:country']   = array('default:city');
    $chains['default2:customer'] = array('default:customer_order');

    return $chains;
  }
}
