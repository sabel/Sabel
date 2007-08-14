<?php

class Test_DB_Test extends SabelTestCase
{
  public static $db = "";
  public static $tables = array("member", "member_sub_group", "member_group",
                                "super_group", "location", "condition_test");

  public function testClean()
  {
    $tables   = self::$tables;
    $executer = new Executer("Member");

    foreach ($tables as $table) {
      $executer->query("DELETE FROM $table", false, Sabel_DB_Statement::DELETE);
    }
  }

  public function testInsert()
  {
    $data = array("id"   => 1,
                  "name" => "us");

    $executer = new Executer("Location");
    $executer->insert($data);

    $data = array("id"   => 2,
                  "name" => "ja");

    $executer->insert($data);

    $data = array("id"   => 3,
                  "name" => "fr");

    $executer->insert($data);

    //=============================================

    $data = array("id"   => 1,
                  "name" => "sgroup1");

    $executer = new Executer("SuperGroup");
    $executer->insert($data);

    $data = array("id"   => 2,
                  "name" => "sgroup2");

    $executer->insert($data);

    //=============================================

    $data = array("id"   => 1,
                  "name" => "group1",
                  "super_group_id" => 2);

    $executer = new Executer("MemberGroup");
    $executer->insert($data);

    $data = array("id"   => 2,
                  "name" => "group2",
                  "super_group_id" => 1);

    $executer->insert($data);

    //=============================================

    $data = array("id"   => 1,
                  "name" => "sub_group1",
                  "member_group_id" => 2);

    $executer = new Executer("MemberSubGroup");
    $executer->insert($data);

    $data = array("id"   => 2,
                  "name" => "sub_group2",
                  "member_group_id" => 1);

    $executer->insert($data);

    //=============================================

    $data = array("id"      => 1,
                  "name"    => "test1",
                  "email"   => "test1@example.com",
                  "is_temp" => true,
                  "location_id" => 1,
                  "member_sub_group_id" => 2);

    $executer = new Executer("Member");
    $executer->insert($data);

    $threw = false;

    try {
      $executer->insert();
    } catch (Exception $e) {
      $threw = true;
    }

    $this->assertTrue($threw);

    $count = $executer->getCount();
    $this->assertEquals($count, 1);
  }

  public function testSaveInsert()
  {
    $member = MODEL("Member");

    $member->id      = 2;
    $member->name    = "test2";
    $member->email   = "test2@example.com";
    $member->is_temp = true;
    $member->location_id = 2;
    $member->member_sub_group_id = 1;

    $executer = new Executer($member);
    $executer->save();

    $count = $executer->getCount();
    $this->assertEquals($count, 2);
  }

  public function testSelect()
  {
    $executer = new Executer("Member");
    $executer->setConstraint("order", "id ASC");
    $members  = $executer->select();

    $this->assertEquals(count($members), 2);

    $member1 = $members[0];
    $member2 = $members[1];

    $this->assertEquals($member1->id, 1);
    $this->assertEquals($member1->name, "test1");
    $this->assertEquals($member1->email, "test1@example.com");
    $this->assertEquals($member1->is_temp, true);
    $this->assertEquals($member1->location_id, 1);

    $this->assertEquals($member2->id, 2);
    $this->assertEquals($member2->name, "test2");
    $this->assertEquals($member2->email, "test2@example.com");
    $this->assertEquals($member2->is_temp, true);
    $this->assertEquals($member2->location_id, 2);

    $executer->setCondition(1);
    $members = $executer->select();

    $this->assertEquals(count($members), 1);
    $this->assertEquals($members[0]->name, "test1");

    $executer->setCondition(2);
    $members = $executer->select();

    $this->assertEquals(count($members), 1);
    $this->assertEquals($members[0]->name, "test2");
  }

  public function testSelectOrder()
  {
    $executer = new Executer("Member");
    $executer->setConstraint("order", "id DESC");
    $members  = $executer->select();

    $this->assertEquals(count($members), 2);

    $member1 = $members[0];
    $member2 = $members[1];

    $this->assertEquals($member1->id, 2);
    $this->assertEquals($member1->name, "test2");
    $this->assertEquals($member1->email, "test2@example.com");
    $this->assertEquals($member1->is_temp, true);
    $this->assertEquals($member1->location_id, 2);

    $this->assertEquals($member2->id, 1);
    $this->assertEquals($member2->name, "test1");
    $this->assertEquals($member2->email, "test1@example.com");
    $this->assertEquals($member2->is_temp, true);
    $this->assertEquals($member2->location_id, 1);
  }

  public function testSelectOne()
  {
    $executer = new Executer("Member");
    $member1 = $executer->selectOne(1);
    $this->assertTrue($member1->isSelected());

    $executer = new Executer("Member");
    $member2 = $executer->selectOne(2);
    $this->assertTrue($member2->isSelected());

    $executer = new Executer("Member");
    $member3 = $executer->selectOne(3);
    $this->assertFalse($member3->isSelected());

    $this->assertEquals($member2->id, 2);
    $this->assertEquals($member2->name, "test2");
  }

  public function testUpdate()
  {
    $data = array("is_temp" => false);

    $executer = new Executer("Member");
    $executer->setCondition(1);
    $executer->update($data);

    $executer = new Executer("Member");

    $threw = false;

    try {
      $executer->update();
    } catch (Exception $e) {
      $threw = true;
    }

    $this->assertTrue($threw);

    $executer = new Executer("Member");
    $member1 = $executer->selectOne(1);
    $this->assertFalse($member1->is_temp);

    $executer = new Executer("Member");
    $member2 = $executer->selectOne(2);
    $this->assertTrue($member2->is_temp);
  }

  public function testSaveUpdate()
  {
    $executer = new Executer("Member");
    $member2 = $executer->selectOne(2);
    $member2->is_temp = false;

    $executer->setModel($member2);
    $executer->save();

    $executer = new Executer("Member");
    $member2 = $executer->selectOne(2);
    $this->assertFalse($member2->is_temp);
  }

  public function testParents()
  {
    $executer = new Executer("Member");
    $executer->setParents(array("MemberSubGroup"));
    $executer->setConstraint("order", "Member.id ASC");
    $members = $executer->select();

    $this->assertEquals(count($members), 2);

    $member1 = $members[0];
    $member2 = $members[1];

    $this->assertEquals($member1->id, 1);
    $this->assertEquals($member2->id, 2);

    $this->assertEquals($member1->MemberSubGroup->id, 2);
    $this->assertEquals($member1->MemberSubGroup->name, "sub_group2");
    $this->assertEquals($member2->MemberSubGroup->id, 1);
    $this->assertEquals($member2->MemberSubGroup->name, "sub_group1");
  }

  public function testJoin()
  {
    $executer = new Executer("Member");
    $executer->setConstraint("order", "Member.id ASC");

    $join = new Sabel_DB_Join($executer);
    $members = $join->add(MODEL("MemberSubGroup"))->join();

    $this->assertEquals(count($members), 2);

    $member1 = $members[0];
    $member2 = $members[1];

    $this->assertEquals($member1->id, 1);
    $this->assertEquals($member2->id, 2);

    $this->assertEquals($member1->MemberSubGroup->id, 2);
    $this->assertEquals($member1->MemberSubGroup->name, "sub_group2");
    $this->assertEquals($member2->MemberSubGroup->id, 1);
    $this->assertEquals($member2->MemberSubGroup->name, "sub_group1");

    $executer = new Executer("Member");
    $executer->setConstraint("order", "Member.id ASC");

    $join = new Sabel_DB_Join($executer);
    $relation = new Sabel_DB_Join_Relation(MODEL("MemberSubGroup"));
    $relation->add(MODEL("MemberGroup"));
    $members = $join->add($relation)->join();

    $this->assertEquals(count($members), 2);

    $member1 = $members[0];
    $member2 = $members[1];

    $this->assertEquals($member1->id, 1);
    $this->assertEquals($member2->id, 2);

    $this->assertEquals($member1->MemberSubGroup->id, 2);
    $this->assertEquals($member1->MemberSubGroup->name, "sub_group2");
    $this->assertEquals($member1->MemberSubGroup->MemberGroup->id, 1);
    $this->assertEquals($member1->MemberSubGroup->MemberGroup->name, "group1");
    $this->assertEquals($member2->MemberSubGroup->id, 1);
    $this->assertEquals($member2->MemberSubGroup->name, "sub_group1");
    $this->assertEquals($member2->MemberSubGroup->MemberGroup->id, 2);
    $this->assertEquals($member2->MemberSubGroup->MemberGroup->name, "group2");
  }

  public function testJoinCount()
  {
    $executer = new Executer("Member");

    $join = new Sabel_DB_Join($executer);
    $relation = new Sabel_DB_Join_Relation(MODEL("MemberSubGroup"));
    $relation->add(MODEL("MemberGroup"));
    $count = $join->add($relation)->getCount();

    $this->assertEquals($count, 2);

    $executer = new Executer("Member");
    $executer->setCondition("MemberGroup.name", "group1");

    $join = new Sabel_DB_Join($executer);
    $relation = new Sabel_DB_Join_Relation(MODEL("MemberSubGroup"));
    $relation->add(MODEL("MemberGroup"));
    $count = $join->add($relation)->getCount();

    $this->assertEquals($count, 1);
  }

  public function testInserts()
  {
    $data = array();

    $data[] = array("id"      => 3,
                    "name"    => "test3",
                    "email"   => "test3@example.com",
                    "is_temp" => true,
                    "location_id" => 1,
                    "member_sub_group_id" => 2,
                    "updated_at" => "2007-01-01 00:00:00",
                    "created_at" => "2007-01-01 00:00:00");

    $data[] = array("id"      => 4,
                    "name"    => "test4",
                    "email"   => "test4@example.com",
                    "is_temp" => true,
                    "location_id" => 2,
                    "member_sub_group_id" => 1,
                    "updated_at" => "2007-01-01 00:00:00",
                    "created_at" => "2007-01-01 00:00:00");

    $data[] = array("id"      => 5,
                    "name"    => "test5",
                    "email"   => "test5@example.com",
                    "is_temp" => true,
                    "location_id" => 3,
                    "member_sub_group_id" => 1,
                    "updated_at" => "2007-01-01 00:00:00",
                    "created_at" => "2007-01-01 00:00:00");

    $executer = new Executer("Member");

    foreach ($data as $values) {
      $executer->insert($values);
    }

    $count = $executer->getCount();
    $this->assertEquals($count, 5);
  }

  public function testGetChild()
  {
    $executer = new Executer("MemberSubGroup");
    $subGroup = $executer->selectOne(1);

    $this->assertTrue($subGroup->isSelected());
    $this->assertEquals($subGroup->id, 1);

    $executer->setModel($subGroup);
    $constraints = array("order" => "Member.id ASC");
    $members = $executer->getChild("Member", $constraints);

    $this->assertEquals(count($members), 3);

    $member1 = $members[0];
    $member2 = $members[1];
    $member3 = $members[2];

    $this->assertEquals($member1->id, 2);
    $this->assertEquals($member1->name, "test2");
    $this->assertEquals($member2->id, 4);
    $this->assertEquals($member2->name, "test4");
    $this->assertEquals($member3->id, 5);
    $this->assertEquals($member3->name, "test5");
  }

  public function testBeforeValidate()
  {
    $member = MODEL("Member");
    $member->name    = "test6";
    $member->email   = "test6@example.com";
    $member->is_temp = true;
    $member->location_id = 1;
    $member->member_sub_group_id = 1;

    $executer = new Executer($member);
    $result = $executer->save(array());

    $this->assertEquals(count($result), 1);
    $this->assertEquals($result[0], "please enter a id.");

    //==============================================

    $member = MODEL("Member");
    $member->email   = "test6@example.com";
    $member->is_temp = true;
    $member->location_id = 1;
    $member->member_sub_group_id = 1;

    $executer = new Executer($member);
    $result = $executer->save(array());

    $this->assertEquals(count($result), 2);

    //==============================================

    $member = MODEL("Member");
    $member->email   = "test6@example.com";
    $member->is_temp = true;
    $member->location_id = 1;
    $member->member_sub_group_id = 1;

    $ignores  = array("name");
    $executer = new Executer($member);
    $result = $executer->save($ignores);

    $this->assertEquals(count($result), 1);
  }

  public function testConditionTest()
  {
    $data = array("bool_flag" => true);

    $executer = new Executer("ConditionTest");
    $newId = $executer->insert($data);
    $this->assertTrue(is_numeric($newId));

    //==============================================

    $data = array("point"     => 200,
                  "bool_flag" => false);

    $executer->insert($data);

    //==============================================

    $data = array("name"      => "name3",
                  "point"     => 300,
                  "bool_flag" => true);

    $executer->insert($data);

    //==============================================

    $data = array("name"      => "name4",
                  "point"     => 400,
                  "bool_flag" => false);

    $executer->insert($data);

    //==============================================

    $model = MODEL("ConditionTest");
    $model->name  = "name5";
    $model->point = 500;
    $model->bool_flag = false;

    $executer = new Executer($model);
    $saved = $executer->save();

    // new sequence id.
    $this->assertTrue(is_int($saved->id));

    //==============================================

    $data = array("name"      => "name%",
                  "point"     => 600,
                  "bool_flag" => false);

    $executer->insert($data);

    //==============================================

    // boolean condition.
    $executer = new Executer("ConditionTest");
    $models = $executer->select("bool_flag", false);
    $this->assertEquals(count($models), 4);

    $count = $executer->getCount("bool_flag", true);
    $this->assertEquals($count, 2);

    // normal condition.
    $executer = new Executer("ConditionTest");
    $model = $executer->selectOne("point", 400);
    $this->assertTrue($model->isSelected());
    $this->assertEquals($model->point, 400);

    $executer = new Executer("ConditionTest");
    $executer->setCondition("point", 400);
    $model = $executer->selectOne();
    $this->assertTrue($model->isSelected());
    $this->assertEquals($model->point, 400);

    // and condition.
    $executer = new Executer("ConditionTest");
    $executer->setCondition("point", 400);
    $executer->setCondition("name", "name4");
    $model = $executer->selectOne();
    $this->assertTrue($model->isSelected());
    $this->assertEquals($model->point, 400);
    $this->assertEquals($model->name, "name4");

    $executer = new Executer("ConditionTest");
    $executer->setCondition("point", 400);
    $executer->setCondition("name", "name5");
    $model = $executer->selectOne();
    $this->assertFalse($model->isSelected());

    // or condition.
    $executer = new Executer("ConditionTest");
    $or = new Sabel_DB_Condition_Or();
    $or->add(new Sabel_DB_Condition_Object("point", 400));
    $or->add(new Sabel_DB_Condition_Object("name", "name5"));
    $executer->loadConditionManager()->add($or);
    $executer->setConstraint("order", "id ASC");
    $models = $executer->select();
    $this->assertEquals(count($models), 2);
    $this->assertEquals($models[0]->point, 400);
    $this->assertEquals($models[1]->name, "name5");

    // between condition.
    $executer = new Executer("ConditionTest");
    $executer->setCondition(new Sabel_DB_Condition_Object("point", array(200, 400), BETWEEN));
    $executer->setConstraint("order", "point DESC");
    $models = $executer->select();
    $this->assertEquals(count($models), 3);
    $this->assertEquals($models[0]->point, 400);
    $this->assertEquals($models[1]->point, 300);
    $this->assertEquals($models[2]->point, 200);

    // compare condition.
    $executer = new Executer("ConditionTest");
    $executer->setCondition(new Sabel_DB_Condition_Object("point", array("<", 400), COMPARE));
    $executer->setConstraint("order", "point DESC");
    $models = $executer->select();
    $this->assertEquals(count($models), 3);
    $this->assertEquals($models[0]->point, 300);
    $this->assertEquals($models[1]->point, 200);
    $this->assertEquals($models[2]->point, 100);

    // or compare condition.
    $executer = new Executer("ConditionTest");
    $or = new Sabel_DB_Condition_Or();
    $or->add(new Sabel_DB_Condition_Object("point", array(">=", 400), COMPARE));
    $or->add(new Sabel_DB_Condition_Object("point", array("<=", 200), COMPARE));
    $executer->loadConditionManager()->add($or);
    $executer->setConstraint("order", "point DESC");
    $models = $executer->select();
    $this->assertEquals(count($models), 5);
    $this->assertEquals($models[0]->point, 600);
    $this->assertEquals($models[1]->point, 500);
    $this->assertEquals($models[2]->point, 400);
    $this->assertEquals($models[3]->point, 200);
    $this->assertEquals($models[4]->point, 100);

    // like condition.
    $executer = new Executer("ConditionTest");
    $executer->setCondition(new Sabel_DB_Condition_Object("name", array("name_", false), LIKE));
    $executer->setConstraint("order", "id ASC");
    $models = $executer->select();
    $this->assertEquals(count($models), 4);
    $this->assertEquals($models[0]->name, "name3");
    $this->assertEquals($models[1]->name, "name4");
    $this->assertEquals($models[2]->name, "name5");
    $this->assertEquals($models[3]->name, "name%");

    $executer = new Executer("ConditionTest");
    $executer->setCondition(new Sabel_DB_Condition_Object("name", "name%", LIKE));
    $models = $executer->select();
    $this->assertEquals(count($models), 1);

    $executer = new Executer("ConditionTest");
    $executer->setCondition(new Sabel_DB_Condition_Object("name", array("nam%", false), LIKE));
    $executer->setConstraint("order", "id ASC");
    $models = $executer->select();
    $this->assertEquals(count($models), 4);
    $this->assertEquals($models[0]->name, "name3");
    $this->assertEquals($models[1]->name, "name4");
    $this->assertEquals($models[2]->name, "name5");
    $this->assertEquals($models[3]->name, "name%");
  }

  public function testClear()
  {
    Sabel_DB_Schema::clear();
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

class Executer extends Sabel_DB_Model_Executer
{
  const INSERT_DATETIME_COLUMN = "created_at";
  const UPDATE_DATETIME_COLUMN = "updated_at";

  public function before($method)
  {
    switch ($method) {
      case "save":
        return $this->beforeSave();
      case "insert":
        return $this->beforeInsert();
      case "update":
        return $this->beforeUpdate();
    }
  }

  public function after($method, $result)
  {
    $this->log();
  }

  private function beforeSave()
  {
    $model = $this->model;

    $columns  = $model->getColumnNames();
    $datetime = $this->now();

    if (in_array(self::UPDATE_DATETIME_COLUMN, $columns)) {
      $model->{self::UPDATE_DATETIME_COLUMN} = $datetime;
    }

    if (!$model->isSelected()) {
      if (in_array(self::INSERT_DATETIME_COLUMN, $columns)) {
        $model->{self::INSERT_DATETIME_COLUMN} = $datetime;
      }
    }

    $args = $this->arguments;

    if (isset($args[0]) && is_array($args[0])) {
      $validator = new Sabel_DB_Validator($model);
      $errors = $validator->validate($args[0]);
      if ($errors) return $errors;
    }
  }

  private function beforeInsert()
  {
    $columns  = $this->model->getColumnNames();
    $datetime = $this->now();

    if (!isset($this->arguments[0])) {
      return null;
    }

    if (in_array(self::UPDATE_DATETIME_COLUMN, $columns)) {
      $this->arguments[0][self::UPDATE_DATETIME_COLUMN] = $datetime;
    }

    if (in_array(self::INSERT_DATETIME_COLUMN, $columns)) {
      $this->arguments[0][self::INSERT_DATETIME_COLUMN] = $datetime;
    }
  }

  private function beforeUpdate()
  {
    $columns = $this->model->getColumnNames();

    if (!isset($this->arguments[0])) {
      return null;
    }

    if (in_array(self::UPDATE_DATETIME_COLUMN, $columns)) {
      $this->arguments[0][self::UPDATE_DATETIME_COLUMN] = $this->now();
    }
  }

  private function now()
  {
    return date("Y-m-d H:i:s");
  }

  private function log()
  {
    $stmt = $this->stmt;

    if (is_object($stmt)) {
      $sql = $stmt->getSql();
      switch ($stmt->getStatementType()) {

        /**
         * select sql log.
         */
        case Sabel_DB_Statement::SELECT:

          break;

        /**
         * insert sql log.
         */
        case Sabel_DB_Statement::INSERT:

          break;

        /**
         * update sql log.
         */
        case Sabel_DB_Statement::UPDATE:

          break;

        /**
         * delete sql log.
         */
        case Sabel_DB_Statement::DELETE:

          break;
      }
    }
  }
}
