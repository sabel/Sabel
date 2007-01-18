<?php

class Test_DB_Test extends SabelTestCase
{
  public static $db = '';
  public static $TABLES = array('basic', 'users', 'city', 'country', 'company',
                                'test_for_like', 'test_condition', 'blog',
                                'customer_order', 'classification', 'favorite_item',
                                'student', 'course', 'student_course', 'timer', 'child');


  public function testBasic()
  {
    $basic = Sabel_Model::load('Basic');
    $basic->id   = 1;
    $basic->name = 'basic name1';
    $basic->save();

    $basic = Sabel_Model::load('Basic');
    $basic->save(array('id' => 2, 'name' => 'basic name2'));

    $basic = Sabel_Model::load('Basic');
    $model = $basic->selectOne(1);
    $this->assertEquals($model->name, 'basic name1');

    $model = Sabel_Model::load('Basic')->selectOne('name', 'basic name2');
    $this->assertEquals((int)$model->id, 2);
  }

  public function testParent()
  {
    $shop = Sabel_Model::load('Company');
    $shop->save(array('id' => 1, 'name' => 'tokyo-company1',     'city_id' => 1));
    $shop->save(array('id' => 2, 'name' => 'tokyo-company2',     'city_id' => 1));
    $shop->save(array('id' => 3, 'name' => 'osaka-company1',     'city_id' => 2));
    $shop->save(array('id' => 4, 'name' => 'osaka-company2',     'city_id' => 2));
    $shop->save(array('id' => 5, 'name' => 'san diego-company1', 'city_id' => 3));
    $shop->save(array('id' => 6, 'name' => 'san diego-company2', 'city_id' => 3));
    $shop->save(array('id' => 7, 'name' => 'rondon-company1',    'city_id' => 4));
    $shop->save(array('id' => 8, 'name' => 'rondon-company2',    'city_id' => 4));

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
    $users->multipleInsert($data);

    $data = array();
    $data[] = array('id' => 1, 'name' => 'tokyo',     'classification_id' => 1, 'country_id' => 1);
    $data[] = array('id' => 2, 'name' => 'osaka',     'classification_id' => 2, 'country_id' => 1);
    $data[] = array('id' => 3, 'name' => 'san diego', 'classification_id' => 2, 'country_id' => 2);
    $data[] = array('id' => 4, 'name' => 'rondon',    'classification_id' => 1, 'country_id' => 3);

    $city = Sabel_Model::load('City');
    $city->multipleInsert($data);

    $city = Sabel_Model::load('Classification');
    $city->save(array('id' => 1, 'class_name' => 'classname1'));
    $city->save(array('id' => 2, 'class_name' => 'classname2'));

    $data = array();
    $data[] = array('id' => 1, 'name' => 'japan');
    $data[] = array('id' => 2, 'name' => 'usa');
    $data[] = array('id' => 3, 'name' => 'england');

    $country = new Country();
    $country->multipleInsert($data);

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

  public function testLike()
  {
    $model = Sabel_Model::load('TestForLike');
    $model->save(array('string' => 'aaa'));
    $model->save(array('string' => 'aa_'));
    $model->save(array('string' => 'aba'));
    $newModel = $model->save(array('string' => 'a%a'));
    $this->assertTrue(is_numeric($newModel->id));
    $this->assertTrue(($newModel->id > 0));
    $this->assertEquals($newModel->string, 'a%a');

    $model = Sabel_Model::load('TestForLike');
    $model->setCondition('LIKE_string', 'aa_');
    $result = $model->select();

    $this->assertTrue(is_array($result));
    $this->assertEquals(count($result), 1);
    $this->assertEquals($result[0]->string, 'aa_');

    $model = Sabel_Model::load('TestForLike');
    $model->setCondition('LIKE_string', array('aa_', false));
    $result = $model->select();

    $this->assertEquals(count($result), 2);
    $this->assertEquals($result[0]->string, 'aaa');
    $this->assertEquals($result[1]->string, 'aa_');

    $model = Sabel_Model::load('TestForLike');
    $model->scond('LIKE_string', 'a%a');
    $result = $model->select();

    $this->assertEquals(count($result), 1);
    $this->assertEquals($result[0]->string, 'a%a');

    $model = Sabel_Model::load('TestForLike');
    $model->scond('LIKE_string', array('a%a', false));
    $result = $model->select();

    $this->assertEquals(count($result), 3);
    $this->assertEquals($result[0]->string, 'aaa');
    $this->assertEquals($result[1]->string, 'aba');
    $this->assertEquals($result[2]->string, 'a%a');
  }

  public function testCondition()
  {
    $model = Sabel_Model::load('TestCondition');
    $model->save(array('status' => __TRUE__,  'registed' => '2005-10-01 10:10:10', 'point' => 1000));
    $model->save(array('status' => __FALSE__, 'registed' => '2005-09-01 10:10:10', 'point' => 2000));
    $model->save(array('status' => __FALSE__, 'registed' => '2005-08-01 10:10:10', 'point' => 3000));
    $model->save(array('status' => __TRUE__,  'registed' => '2005-07-01 10:10:10', 'point' => 4000));
    $model->save(array('status' => __FALSE__, 'registed' => '2005-06-01 10:10:10', 'point' => 5000));
    $model->save(array('status' => __FALSE__, 'registed' => '2005-05-01 10:10:10', 'point' => 6000));
    $model->save(array('status' => __TRUE__,  'registed' => '2005-04-01 10:10:10', 'point' => 7000));
    $model->save(array('status' => __FALSE__, 'registed' => '2005-03-01 10:10:10', 'point' => 8000));
    $model->save(array('status' => __FALSE__, 'registed' => '2005-02-01 10:10:10', 'point' => 9000));
    $model->save(array('status' => __TRUE__,  'registed' => '2005-01-01 10:10:10', 'point' => 10000));

    $model = Sabel_Model::load('TestCondition');
    $model->scond('COMP_point', array('>=', 8000));
    $models = $model->select();
    $this->assertEquals(count($models), 3);

    $models = Sabel_Model::load('TestCondition')->select('COMP_point', array('>=', 8000));
    $this->assertEquals(count($models), 3);

    $models = Sabel_Model::load('TestCondition')->select('COMP_point', array('>', 8000));
    $this->assertEquals(count($models), 2);

    $model = Sabel_Model::load('TestCondition');
    $conditions = array();
    $conditions[] = new Sabel_DB_Condition('COMP_point', array('>=', 8000));
    $conditions[] = new Sabel_DB_Condition('COMP_point', array('<=', 3000));
    $model->setCondition($conditions);
    $models = $model->select();
    $this->assertEquals(count($models), 6);

    $model = Sabel_Model::load('TestCondition');
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

    $model = Sabel_Model::load('TestCondition');
    $model->scond('status', __FALSE__);
    $models = $model->select();
    $this->assertEquals(count($models), 6);

    $model1 = $models[0];
    $model2 = $models[1];
    $model3 = $models[2];

    $this->assertEquals($model1->point, 2000);
    $this->assertEquals($model2->point, 3000);
    $this->assertEquals($model3->point, 5000);

    $model->unsetCondition();
    $model->scond('status', __FALSE__, Sabel_DB_Condition::NOT);
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

    $model = Sabel_Model::load('TestCondition');
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

    $model = Sabel_Model::load('TestCondition');
    $model->save(array('status' => __FALSE__, 'registed' => '2004-12-01 10:10:10'));
    $model->save(array('status' => __FALSE__, 'registed' => '2004-11-01 10:10:10'));
    $model->save(array('status' => __TRUE__,  'registed' => '2004-10-01 10:10:10', 'point' => 13000));

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

    $model = Sabel_Model::load('TestCondition');
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

  public function testFirst()
  {
    $model = Sabel_Model::load('TestCondition');
    $model = $model->getFirst('registed');

    $this->assertTrue($model->status);
    $this->assertEquals($model->registed, '2004-10-01 10:10:10');
    $this->assertEquals($model->point, 13000);

    $executer = new Sabel_DB_Executer(array('table' => 'test_condition'));
    $row = $executer->getFirst('registed');

    switch (self::$db) {
      case 'MYSQL':
        $this->assertEquals($row['status'], '1');
        break;
      case 'PGSQL':
        $this->assertTrue($row['status']);
        break;
      case 'SQLITE':
        $this->assertEquals($row['status'], 'true');
        break;
    }

    $this->assertEquals($row['registed'], '2004-10-01 10:10:10');
    $this->assertEquals((int)$row['point'], 13000);
  }

  public function testLast()
  {
    $model = Sabel_Model::load('TestCondition');
    $model = $model->getLast('registed');
    $this->assertTrue($model->status);
    $this->assertEquals($model->registed, '2005-10-01 10:10:10');
    $this->assertEquals($model->point, 1000);

    $executer = new Sabel_DB_Executer(array('table' => 'test_condition'));
    $row = $executer->getLast('registed');

    switch (self::$db) {
      case 'MYSQL':
        $this->assertEquals($row['status'], '1');
        break;
      case 'PGSQL':
        $this->assertTrue($row['status']);
        break;
      case 'SQLITE':
        $this->assertEquals($row['status'], 'true');
        break;
    }

    $this->assertEquals($row['registed'], '2005-10-01 10:10:10');
    $this->assertEquals((int)$row['point'], 1000);
  }

  public function testChildConstraint()
  {
    $blog = Sabel_Model::load('Blog');
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

    $favorite = Sabel_Model::load('FavoriteItem');
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

    // use default child constraint in users model.
    $this->assertEquals($blog1->write_date->getDateTime(), '2005-01-01 04:01:01');
    $this->assertEquals($blog2->write_date->getDateTime(), '2005-01-01 03:01:01');
    $this->assertEquals($blog3->write_date->getDateTime(), '2005-01-01 02:01:01');
    $this->assertEquals($blog4->write_date->getDateTime(), '2005-01-01 01:01:01');

    $user  = new Users(1);
    $user->cconst('Blog', array('order' => 'write_date'));
    $blogs = $user->getChild('Blog');
    $this->assertEquals(count($blogs), 4);

    $blog1 = $blogs[0];
    $blog2 = $blogs[1];
    $blog3 = $blogs[2];
    $blog4 = $blogs[3];

    // child constraint is overrided.
    $this->assertEquals($blog1->write_date->getDateTime(), '2005-01-01 01:01:01');
    $this->assertEquals($blog2->write_date->getDateTime(), '2005-01-01 02:01:01');
    $this->assertEquals($blog3->write_date->getDateTime(), '2005-01-01 03:01:01');
    $this->assertEquals($blog4->write_date->getDateTime(), '2005-01-01 04:01:01');

    $user  = new Users(2);
    $user->cconst('FavoriteItem', array('order' => 'registed asc'));
    $blogs = $user->getChild('Blog');
    $items = $user->getChild('FavoriteItem');

    $this->assertEquals(count($blogs), 3);
    $this->assertEquals(count($items), 2);

    $blog1 = $blogs[0];
    $blog2 = $blogs[1];
    $blog3 = $blogs[2];

    $this->assertEquals($blog1->write_date->getDateTime(), '2005-01-01 07:01:01');
    $this->assertEquals($blog2->write_date->getDateTime(), '2005-01-01 06:01:01');
    $this->assertEquals($blog3->write_date->getDateTime(), '2005-01-01 05:01:01');

    $item1 = $items[0];
    $item2 = $items[1];

    $this->assertEquals($item1->registed->getDateTime(), '2005-12-02 01:01:01');
    $this->assertEquals($item2->registed->getDateTime(), '2005-12-03 01:01:01');
  }

  public function testChildPaginate()
  {
    $user  = new Users(1);
    $user->cconst('Blog', array('order' => 'write_date desc', 'limit' => 2));
    $blogs = $user->getChild('Blog');
    $this->assertEquals(count($blogs), 2);

    $blog1 = $blogs[0];
    $blog2 = $blogs[1];

    $this->assertEquals($blog1->write_date->getDateTime(), '2005-01-01 04:01:01');
    $this->assertEquals($blog2->write_date->getDateTime(), '2005-01-01 03:01:01');

    $user  = new Users(1);
    $user->cconst('Blog', array('order' => 'write_date desc', 'limit' => 2, 'offset' => 2));
    $blogs = $user->getChild('Blog');
    $this->assertEquals(count($blogs), 2);

    $blog1 = $blogs[0];
    $blog2 = $blogs[1];

    $this->assertEquals($blog1->write_date->getDateTime(), '2005-01-01 02:01:01');
    $this->assertEquals($blog2->write_date->getDateTime(), '2005-01-01 01:01:01');
  }

  public function testTest()
  {
    $model = Sabel_Model::load('Customer');
    $model->execute('DELETE FROM customer');

    $model->save(array('id' => 1, 'name' => 'name1'));
    $model->save(array('id' => 2, 'name' => 'name2'));

    $order = Sabel_Model::load('CustomerOrder');
    $order->save(array('customer_id' => 1, 'buy_date' => '2005-01-01 10:10:10', 'amount' => 1000));
    $order->save(array('customer_id' => 1, 'buy_date' => '2005-02-01 10:10:10', 'amount' => 2000));
    $order->save(array('customer_id' => 1, 'buy_date' => '2005-03-01 10:10:10', 'amount' => 3000));
    $order->save(array('customer_id' => 1, 'buy_date' => '2005-04-01 10:10:10', 'amount' => 4000));
    $order->save(array('customer_id' => 1, 'buy_date' => '2005-05-01 10:10:10', 'amount' => 5000));
    $order->save(array('customer_id' => 2, 'buy_date' => '2005-06-01 10:10:10', 'amount' => 6000));
    $order->save(array('customer_id' => 2, 'buy_date' => '2005-07-01 10:10:10', 'amount' => 7000));
    $order->save(array('customer_id' => 2, 'buy_date' => '2005-08-01 10:10:10', 'amount' => 8000));
  }

  public function testDBRelation()
  {
    $customer = Sabel_Model::load('Customer')->selectOne(2);
    $orders   = $customer->getChild('CustomerOrder');
    $this->assertEquals(count($orders), 3);

    $order1 = $orders[0];
    $order2 = $orders[1];
    $order3 = $orders[2];

    $this->assertEquals($order1->amount, 6000);
    $this->assertEquals($order2->amount, 7000);
    $this->assertEquals($order3->amount, 8000);

    $model = Sabel_Model::load('CustomerOrder');
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

    $customer = Sabel_Model::load('Customer')->selectOne(2);
    $order    = $customer->newChild('CustomerOrder');

    $order->buy_date = '2005-08-01 10:10:10';
    $order->amount   = 9000;
    $order->save();

    $orders = Sabel_Model::load('Customer')->selectOne(2)->getChild('CustomerOrder');
    $this->assertEquals(count($orders), 4);

    $order = MODEL('CustomerOrder');
    $order->setParents(array('Customer'));
    $order->sconst('order', 'CustomerOrder.id');
    $orders = $order->select();

    $this->assertEquals($orders[0]->customer_id, 1);
    $this->assertEquals($orders[0]->buy_date, '2005-01-01 10:10:10');
    $this->assertEquals($orders[0]->amount, 1000);

    $this->assertEquals($orders[1]->customer_id, 1);
    $this->assertEquals($orders[1]->buy_date, '2005-02-01 10:10:10');
    $this->assertEquals($orders[1]->amount, 2000);

    $this->assertEquals($orders[5]->customer_id, 2);
    $this->assertEquals($orders[5]->buy_date, '2005-06-01 10:10:10');
    $this->assertEquals($orders[5]->amount, 6000);
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
    $modelPairs = array('Users:City', 'Users:Company', 'City:Country', 'City:Classification', 'Company:City');
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

    $this->assertEquals($user4->Company->id, 2);
    $this->assertEquals($user4->City->id, 1);
    $this->assertEquals($user4->City->name, 'tokyo');
    $this->assertEquals($user4->Company->name, 'tokyo-company2');
    $this->assertEquals($user4->Company->City->id, 1);
    $this->assertEquals($user4->Company->City->name, 'tokyo');
  }

  public function testFusionModel()
  {
    $model = Sabel_Model::fusion(array('Users', 'City', 'Country'));
    $model->setCombination(array('Users:City', 'City:Country'));
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

  public function testFusionCondition()
  {
    $model = Sabel_Model::fusion(array('Users', 'City', 'Country'));
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

    $model = Sabel_Model::fusion(array('Users', 'City', 'Country'));
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
    $model = Sabel_Model::fusion(array('Users', 'City', 'Classification', 'Country'));
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
    // ok.
    //$model = Sabel_Model::fusion(array('City', 'Users', 'Classification', 'Country'));
    //$model->setCombination(array('Users:City', 'City:Classification', 'City:Country'));

    $model = Sabel_Model::fusion(array('City', 'Users', 'Classification', 'Country'));
    $model->setCombination(array('Users:City', 'City:Country', 'City:Classification'));

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
    $model = Sabel_Model::fusion(array('Users', 'City', 'Classification', 'Country'));
    $model->setCombination(array('Users:City', 'City:Classification', 'City:Country'));
    $fusioned = $model->selectOne('id', 4);

    $fusioned->city_id = 2;
    $fusioned->save();

    $model = Sabel_Model::fusion(array('Users', 'City', 'Classification', 'Country'));
    $model->setCombination(array('Users:City', 'City:Classification', 'City:Country'));
    $fusioned = $model->selectOne('id', 4);

    $this->assertEquals((int)$fusioned->id, 4);
    $this->assertEquals((int)$fusioned->city_id, 2);
    $this->assertEquals((int)$fusioned->City_id, 2);
    $this->assertEquals($fusioned->City_name, 'osaka');

    $model = Sabel_Model::fusion(array('Users', 'City', 'Classification', 'Country'));
    $model->setCombination(array('Users:City', 'City:Classification', 'City:Country'));
    $fusioned = $model->selectOne('id', 4);

    $fusioned->City_name = 'Osaka';
    $fusioned->save();

    $model = Sabel_Model::fusion(array('Users', 'City', 'Classification', 'Country'));
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
    $model = Sabel_Model::load('TestCondition');
    $this->assertEquals($model->getCount(), 13);
    $model->unsetCondition();
    $model->remove('point', 1000);

    $model = Sabel_Model::load('TestCondition');
    $this->assertEquals($model->getCount(), 12);
    $model->unsetCondition();

    $model->scond('point', Sabel_DB_Condition::ISNULL);
    $this->assertEquals($model->getCount(), 2);

    Sabel_Model::load('TestCondition')->remove('point', Sabel_DB_Condition::ISNULL);

    $model = Sabel_Model::load('TestCondition');
    $this->assertEquals($model->getCount(), 10);

    $model = Sabel_Model::load('TestCondition');
    $conditions = array();
    $conditions[] = new Sabel_DB_Condition('point', 10000);
    $conditions[] = new Sabel_DB_Condition('COMP_point', array('<=', 4000));
    $model->setCondition($conditions);
    $model->remove();

    $model = Sabel_Model::load('TestCondition');
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

    $customer = Sabel_Model::load('Customer')->selectOne(1);
    $customer->remove();

    $customer = Sabel_Model::load('Customer')->selectOne(2);
    $customer->remove();
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

    $model  = Sabel_Model::load('City');
    $cities = $model->select();

    $this->assertEquals(count($cities), 2);
    $this->assertEquals($cities[0]->name, 'san diego');
    $this->assertEquals($cities[1]->name, 'rondon');

    $users = new Users();
    $users->sconst('order', 'users.id');
    $users = $users->select();

    $this->assertEquals(count($users), 2);
    $this->assertEquals($users[0]->name, 'username1');
    $this->assertEquals($users[1]->name, 'username2');
  }

  public function testTransaction()
  {
    Sabel_Model::load('CustomerOrder')->execute('DELETE FROM customer_order');

    $customers = Sabel_Model::load('Customer')->select();
    $this->assertFalse($customers);

    $orders = Sabel_Model::load('CustomerOrder')->select();
    $this->assertFalse($orders);

    $model = Sabel_Model::load('CustomerOrder');
    $model->begin(); // db1 start transaction.
    $model->save(array('customer_id' => 1, 'buy_date' => '1000-01-01 01:01:01', 'amount' => 1000));
    $model->save(array('customer_id' => 1, 'buy_date' => '1000-01-01 01:01:01', 'amount' => 1000));
    $model->save(array('customer_id' => 1, 'buy_date' => '1000-01-01 01:01:01', 'amount' => 1000));

    $model = Sabel_Model::load('Customer');
    $model->begin(); // db2 start transaction.
    $model->save(array('id' => 1, 'name' => 'name'));
    $model->save(array('id' => 2, 'name' => 'name'));
    // 'nama' not found -> execute rollback.
    try { @$model->save(array('id' => 3, 'nama' => 'name')); } catch (Exception $e) {}

    // not execute.
    $model->commit();

    $customers = Sabel_Model::load('Customer')->select();
    $this->assertFalse($customers);

    $orders = Sabel_Model::load('CustomerOrder')->select();
    $this->assertFalse($orders);
  }

  public function testDatabasesCasecadeDelete()
  {
    $model = Sabel_Model::load('CustomerOrder');
    $model->save(array('customer_id' => 1, 'buy_date' => '1000-01-01 01:01:01', 'amount' => 1000));
    $model->save(array('customer_id' => 1, 'buy_date' => '1000-01-01 01:01:01', 'amount' => 1000));
    $model->save(array('customer_id' => 1, 'buy_date' => '1000-01-01 01:01:01', 'amount' => 1000));
    $model->save(array('customer_id' => 2, 'buy_date' => '2000-02-02 02:02:02', 'amount' => 2000));

    $model = Sabel_Model::load('Customer');
    $model->save(array('id' => 1, 'name' => 'name1'));
    $model->save(array('id' => 2, 'name' => 'name2'));
    $model->save(array('id' => 3, 'name' => 'name3'));

    $model = Sabel_Model::load('Customer')->cascadeDelete(1);
    $this->assertEquals(Sabel_Model::load('Customer')->getCount(), 2);

    $model = Sabel_Model::load('Customer')->select();
    $cus1  = $model[0];
    $cus2  = $model[1];

    $this->assertEquals($cus1->id, 2);
    $this->assertEquals($cus1->name, 'name2');
    $this->assertEquals($cus2->id, 3);
    $this->assertEquals($cus2->name, 'name3');

    $model = Sabel_Model::load('CustomerOrder')->select();
    $this->assertEquals(count($model), 1);
    $this->assertEquals($model[0]->customer_id, 2);
    $this->assertEquals($model[0]->buy_date, '2000-02-02 02:02:02');
    $this->assertEquals($model[0]->amount, 2000);

    $executer = new Sabel_DB_Executer(array('table' => 'customer', 'connectName' => 'default2'));
    $executer->executeQuery('DELETE FROM customer');

    $executer = new Sabel_DB_Executer(array('table' => 'customer', 'connectName' => 'default'));
    $executer->executeQuery('DELETE FROM customer_order');
  }

  public function testUpdate()
  {
    $model = Sabel_Model::load('Customer');
    $model->save(array('id' => 1, 'name' => 'name1'));
    $model->save(array('id' => 2, 'name' => 'name2'));

    $customer = Sabel_Model::load('Customer')->selectOne(1);
    $this->assertEquals($customer->name, 'name1');

    $customer->name = 'new name1';
    $customer = $customer->save();

    $this->assertEquals((int)$customer->id, 1);
    $this->assertEquals($customer->name, 'new name1');

    $customer = Sabel_Model::load('Customer')->selectOne(1);
    $this->assertEquals($customer->name, 'new name1');

    $customer = Sabel_Model::load('Customer')->selectOne(100);
    $this->assertFalse($customer->isSelected());

    $customer->name = 'name100';
    $customer = $customer->save();
    $this->assertEquals((int)$customer->id, 100);
    $this->assertEquals($customer->name, 'name100');

    $customer = Sabel_Model::load('Customer')->selectOne(100);
    $this->assertTrue($customer->isSelected());
    $this->assertEquals($customer->name, 'name100');

    $model = Sabel_Model::load('CustomerOrder');
    $model->scond(5);
    $model->scond('customer_id', 10);
    $order = $model->selectOne();
    $this->assertFalse($order->isSelected());

    $order->buy_date = '1999-01-01 12:34:55';
    $order->amount   = 9999;
    $order->save();

    $order = Sabel_Model::load('CustomerOrder')->selectOne(5);
    $this->assertTrue($order->isSelected());

    $this->assertEquals($order->id, 5);
    $this->assertEquals($order->customer_id, 10);
    $this->assertEquals($order->buy_date, '1999-01-01 12:34:55');
    $this->assertEquals($order->amount, 9999);
  }

  public function testSchema()
  {
    $model  = Sabel_Model::load('SchemaTest');
    $schema = $model->getTableSchema();

    $id = $schema->id;
    $nm = $schema->name;
    $bl = $schema->bl;
    $dt = $schema->dt;
    $ft = $schema->ft_val;
    $db = $schema->db_val;
    $tx = $schema->tx;

    $this->assertEquals($id->type, Sabel_DB_Type_Const::INT);
    $this->assertEquals($id->max,  2147483647);
    $this->assertEquals($id->min, -2147483648);
    $this->assertFalse($id->nullable);
    $this->assertTrue($id->increment);
    $this->assertTrue($id->primary);

    $this->assertEquals($nm->type, Sabel_DB_Type_Const::STRING);
    $this->assertEquals($nm->max, 128);
    $this->assertFalse($nm->nullable);
    $this->assertFalse($nm->increment);
    $this->assertFalse($nm->primary);
    $this->assertEquals($nm->default, 'test');

    $this->assertEquals($bl->type, Sabel_DB_Type_Const::BOOL);
    $this->assertTrue($bl->nullable);
    $this->assertFalse($bl->increment);
    $this->assertFalse($bl->primary);
    $this->assertFalse($bl->default);

    $this->assertEquals($dt->type, Sabel_DB_Type_Const::DATETIME);
    $this->assertTrue($dt->nullable);
    $this->assertFalse($dt->increment);
    $this->assertFalse($dt->primary);

    $this->assertEquals($ft->type, Sabel_DB_Type_Const::FLOAT);
    $this->assertEquals($ft->max,  3.4028235E38);
    $this->assertEquals($ft->min, -3.4028235E38);
    $this->assertTrue($ft->nullable);
    $this->assertFalse($ft->increment);
    $this->assertFalse($ft->primary);
    $this->assertEquals($ft->default, 1);

    $this->assertEquals($db->type, Sabel_DB_Type_Const::DOUBLE);
    $this->assertEquals($db->max,  1.79769E308);
    $this->assertEquals($db->min, -1.79769E308);
    $this->assertFalse($db->nullable);
    $this->assertFalse($db->increment);
    $this->assertFalse($db->primary);

    $this->assertEquals($tx->type, Sabel_DB_Type_Const::TEXT);
    $this->assertTrue($tx->nullable);
    $this->assertFalse($tx->increment);
    $this->assertFalse($tx->primary);
  }

  public function testBridge()
  {
    $model = Sabel_Model::load('Student');
    $model->save(array('id' => 1, 'name' => 'suzuki'));
    $model->save(array('id' => 2, 'name' => 'satou'));
    $model->save(array('id' => 3, 'name' => 'tanaka'));
    $model->save(array('id' => 4, 'name' => 'koike'));
    $model->save(array('id' => 5, 'name' => 'yamada'));

    $model = Sabel_Model::load('Course');
    $model->save(array('id' => 1, 'course_name' => 'math'));
    $model->save(array('id' => 2, 'course_name' => 'physics'));
    $model->save(array('id' => 3, 'course_name' => 'sience'));

    $model = Sabel_Model::load('StudentCourse');
    $model->save(array('student_id' => 1, 'course_id' => 1));
    $model->save(array('student_id' => 1, 'course_id' => 2));
    $model->save(array('student_id' => 2, 'course_id' => 2));
    $model->save(array('student_id' => 3, 'course_id' => 1));
    $model->save(array('student_id' => 4, 'course_id' => 3));
    $model->save(array('student_id' => 2, 'course_id' => 3));
    $model->save(array('student_id' => 3, 'course_id' => 2));

    $suzuki = Sabel_Model::load('Student')->selectOne(1);
    $this->assertEquals($suzuki->name, 'suzuki');

    $courses = $suzuki->getChild('Course');
    $this->assertEquals(count($courses), 2);

    $courses = $suzuki->Course;
    $this->assertEquals(count($courses), 2);

    $suzuki = Sabel_Model::load('Student')->selectOne(5);
    $this->assertEquals($suzuki->name, 'yamada');

    $courses = $suzuki->getChild('Course');
    $this->assertFalse($courses);
  }

  public function testClearChild()
  {
    $blogs = Sabel_Model::load('Blog')->select();
    $this->assertEquals(count($blogs), 7);

    $user = new Users(2);
    $user->clearChild('Blog');

    $blogs = Sabel_Model::load('Blog')->select();
    $this->assertEquals(count($blogs), 4);
  }

  public function testNewChild()
  {
    $user = new Users(2);
    $blog = $user->newChild('Blog');

    $blog->id         = 8;
    $blog->title      = 'title8';
    $blog->article    = 'article8';
    $blog->write_date = '2005-01-01 08:01:01';
    $blog->save();

    $blog = Sabel_Model::load('Blog')->selectOne(8);
    $this->assertEquals($blog->title, 'title8');
    $this->assertEquals($blog->users_id, 2);
  }

  public function testTimer()
  {
    $model = Sabel_Model::load('Timer');
    $model->save(array('id' => 1));

    $model = Sabel_Model::load('Timer')->selectOne(1);
    $this->assertEquals($model->id, 1);
    $this->assertNotNull($model->auto_update);
    $this->assertNotNull($model->auto_create);

    $model->save(array('auto_create' => date('Y-m-d H:i:s')));

    $model = Sabel_Model::load('Timer')->selectOne(1);
    $this->assertEquals($model->id, 1);
    $this->assertNotNull($model->auto_update);
    $this->assertNotNull($model->auto_create);
  }

  public function testExecuter()
  {
    $prop   = array('table' => 'favorite_item');
    $exe    = new Sabel_DB_Executer($prop);
    $driver = $exe->getDriver();
    $driver->execute("SELECT * FROM favorite_item");
    $results = $driver->getResultSet()->fetchAll();
    $this->assertEquals(count($results), 7);
  }

  public function testExecuterConstraintAndCondition()
  {
    $prop   = array('table' => 'favorite_item');
    $exe    = new Sabel_DB_Executer($prop);

    $driver = $exe->getDriver();
    $exe->getStatement()->setBasicSQL("SELECT * FROM favorite_item");
    $exe->setConstraint(array('order' => 'registed desc'));
    $results = $exe->exec()->fetchAll();

    $row1 = $results[0];
    $row2 = $results[1];
    $row3 = $results[2];

    $this->assertEquals((int)$row1['users_id'], 4);
    $this->assertEquals((int)$row2['users_id'], 1);
    $this->assertEquals((int)$row3['users_id'], 3);

    $prop = array('table' => 'favorite_item');
    $exe  = new Sabel_DB_Executer($prop);
    $exe->setCondition(array('users_id' => 4));
    $results = $exe->select()->fetchAll();

    $this->assertEquals(count($results), 1);

    $row = $results[0];
    $this->assertEquals((int)$row['id'], 7);
    $this->assertEquals((int)$row['users_id'], 4);
  }

  public function testExecuterUpdate()
  {
    $prop = array('table' => 'favorite_item');
    $exe  = new Sabel_DB_Executer($prop);
    $exe->scond(7);
    $exe->update(array('registed' => '2005-12-08 01:01:01', 'name' => 'favorite8'));

    $exe->unsetCondition();

    $exe->scond(7);
    $row = $exe->select()->fetch();

    $this->assertEquals((int)$row['id'], 7);
    $this->assertEquals((int)$row['users_id'], 4);
    $this->assertEquals($row['registed'], '2005-12-08 01:01:01');
    $this->assertEquals($row['name'], 'favorite8');
  }

  public function testExecuterUpdate2()
  {
    $prop = array('table' => 'favorite_item');
    $exe  = new Sabel_DB_Executer($prop);
    $exe->scond('users_id', 3);
    $exe->update(array('users_id' => 5));

    // $exe->unsetCondition();
    // $exe->scond('users_id', 3);

    $row = $exe->select()->fetchAll();
    $this->assertFalse($row);

    $exe->unsetCondition();
    $exe->scond('users_id', 5);

    $row = $exe->select()->fetchAll();
    $this->assertEquals(count($row), 2);
  }

  public function testExecuterInsert()
  {
    $prop  = array('table' => 'test_condition');
    $exe   = new Sabel_DB_Executer($prop);
    $data  = array('status' => __TRUE__, 'registed' => '2006-01-01 10:10:10', 'point' => 20000);
    $newId = $exe->insert($data, 'id');

    $this->assertTrue(is_int($newId));
    $this->assertTrue($newId > 0);

    $exe = new Sabel_DB_Executer($prop);
    $row = $exe->getLast('id');

    $this->assertEquals((int)$row['id'], $newId);

    switch (self::$db) {
      case 'MYSQL':
        $this->assertEquals($row['status'], '1');
        break;
      case 'PGSQL':
        $this->assertTrue($row['status']);
        break;
      case 'SQLITE':
        $this->assertEquals($row['status'], 'true');
        break;
    }

    $this->assertEquals($row['registed'], '2006-01-01 10:10:10');
    $this->assertEquals((int)$row['point'], 20000);

    $model = MODEL('TestCondition');
    $obj   = $model->getLast('id');

    $this->assertEquals($obj->id, $newId);
    $this->assertTrue($obj->status);
    $this->assertEquals($obj->registed, '2006-01-01 10:10:10');
    $this->assertEquals($obj->point, 20000);
  }

  public function testChildConstarint2()
  {
    $data = array();
    $data[] = array('id' => 1, 'name' => 'parent1');
    $data[] = array('id' => 2, 'name' => 'parent2');
    $data[] = array('id' => 3, 'name' => 'parent3');
    MODEL('Parents')->multipleInsert($data);

    $data = array();
    $data[] = array('id' => 1, 'parents_id' => 2, 'name' => 'child1', 'height' => 160);
    $data[] = array('id' => 2, 'parents_id' => 2, 'name' => 'child2', 'height' => 165);
    $data[] = array('id' => 3, 'parents_id' => 3, 'name' => 'child3', 'height' => 170);
    $data[] = array('id' => 4, 'parents_id' => 3, 'name' => 'child4', 'height' => 175);
    $data[] = array('id' => 5, 'parents_id' => 1, 'name' => 'child5', 'height' => 180);
    MODEL('Child')->multipleInsert($data);

    $data = array();
    $data[] = array('id' => 1,  'child_id' => 1, 'name' => 'grand1',  'age' => 9);
    $data[] = array('id' => 2,  'child_id' => 1, 'name' => 'grand2',  'age' => 8);
    $data[] = array('id' => 3,  'child_id' => 2, 'name' => 'grand3',  'age' => 3);
    $data[] = array('id' => 4,  'child_id' => 2, 'name' => 'grand4',  'age' => 2);
    $data[] = array('id' => 5,  'child_id' => 2, 'name' => 'grand5',  'age' => 6);
    $data[] = array('id' => 6,  'child_id' => 3, 'name' => 'grand6',  'age' => 4);
    $data[] = array('id' => 7,  'child_id' => 4, 'name' => 'grand7',  'age' => 2);
    $data[] = array('id' => 8,  'child_id' => 4, 'name' => 'grand8',  'age' => 10);
    $data[] = array('id' => 9,  'child_id' => 5, 'name' => 'grand9',  'age' => 1);
    $data[] = array('id' => 10, 'child_id' => 5, 'name' => 'grand10', 'age' => 5);
    MODEL('GrandChild')->multipleInsert($data);

    $p = MODEL('Parents')->selectOne(2);
    $children = $p->getChild('Child');

    $this->assertEquals($p->name, 'parent2');
    $this->assertEquals(count($children), 2);

    $c1 = $children[0];
    $c2 = $children[1];

    $this->assertEquals($c1->id, 2);
    $this->assertEquals($c2->id, 1);
    $this->assertEquals($c1->height, 165);
    $this->assertEquals($c2->height, 160);

    $gChildren = $c1->GrandChild;
    $this->assertEquals(count($gChildren), 3);

    $g1 = $gChildren[0];
    $g2 = $gChildren[1];
    $g3 = $gChildren[2];

    $this->assertEquals($g1->id, 4);
    $this->assertEquals($g2->id, 3);
    $this->assertEquals($g3->id, 5);

    $this->assertEquals($g1->age, 2);
    $this->assertEquals($g2->age, 3);
    $this->assertEquals($g3->age, 6);

    $p = MODEL('Parents')->selectOne(2);
    $p->cconst('Child', array('order' => 'height'));
    $p->cconst('GrandChild', array('order' => 'age desc'));

    list ($c1, $c2) = $p->getChild('Child');

    $this->assertEquals($c1->id, 1);
    $this->assertEquals($c2->id, 2);
    $this->assertEquals($c1->height, 160);
    $this->assertEquals($c2->height, 165);

    $gChildren = $c2->GrandChild;
    $this->assertEquals(count($gChildren), 3);

    $g1 = $gChildren[0];
    $g2 = $gChildren[1];
    $g3 = $gChildren[2];

    $this->assertEquals($g1->id, 5);
    $this->assertEquals($g2->id, 3);
    $this->assertEquals($g3->id, 4);

    $this->assertEquals($g1->age, 6);
    $this->assertEquals($g2->age, 3);
    $this->assertEquals($g3->age, 2);
  }

  public function testClear()
  {
    Sabel_DB_SimpleCache::clear();
    Sabel_DB_Connection::closeAll();
  }
}

class Users extends Sabel_DB_Model
{
  protected $childConstraints = array('Blog' => array('order' => 'write_date desc'));
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

class StudentCourseBridge extends Sabel_DB_Model_Bridge
{
  protected $bridgeModel = 'StudentCourse';
  protected $parents = array('Student', 'Course');
}

class Student extends StudentCourseBridge
{
}

class Course extends Sabel_DB_Model_Bridge
{
}

class Parents extends Sabel_DB_Model
{
  protected $connectName = 'default2';
  protected $childConstraints = array('Child' => array('order' => 'height desc'));
}

class Child extends Sabel_DB_Model
{
  protected $children = array('GrandChild');
  protected $childConstraints = array('GrandChild' => array('order' => 'age'));
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
    $property = array('primaryKey'   => 'id',
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
    $property = array('primaryKey'   => 'id',
                      'incrementKey' => null,
                      'tableEngine'  => 'InnoDB');

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
    $property = array('primaryKey'   => 'id',
                      'incrementKey' => 'id',
                      'tableEngine'  => 'InnoDB');

    return $property;
  }
}

class Schema_CascadeChain
{
  public static function get()
  {
    $chains = array();

    $chains['users']          = array('blog'); //array('blog','favorite_item');
    $chains['parents']        = array('child');
    $chains['classification'] = array('city');
    $chains['country']        = array('id:city.country_id');
    $chains['city']           = array('id:company','users.city_id');
    $chains['customer']       = array('customer_order.customer_id');
    $chains['student']        = array('student_course');
    $chains['course']         = array('student_course');
    $chains['company']        = array('users');
    $chains['child']          = array('grand_child');

    return $chains;
  }
}
