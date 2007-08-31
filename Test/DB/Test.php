<?php

class Test_DB_Test extends SabelTestCase
{
  public static $db = "";
  public static $tables = array("member", "member_sub_group", "member_group",
                                "super_group", "location", "condition_test",
                                "student_course", "student", "course", "schema_test");

  public function testClean()
  {
    try {
    $tables   = self::$tables;
    $executer = new Manipulator("Member");

    foreach ($tables as $table) {
      $executer->query("DELETE FROM $table", false, Sabel_DB_Statement::DELETE);
    }

    $executer->query("DELETE FROM tree WHERE id > 2");
    $executer->query("DELETE FROM tree");
    } catch (Exception $e) {
      var_dump($e->getMessage()); exit;
    }
  }

  public function testInsert()
  {
    $data = array("id"   => 1,
                  "name" => "us");

    $executer = new Manipulator("Location");
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

    $executer = new Manipulator("SuperGroup");
    $executer->insert($data);

    $data = array("id"   => 2,
                  "name" => "sgroup2");

    $executer->insert($data);

    //=============================================

    $data = array("id"   => 1,
                  "name" => "group1",
                  "super_group_id" => 2);

    $executer = new Manipulator("MemberGroup");
    $executer->insert($data);

    $data = array("id"   => 2,
                  "name" => "group2",
                  "super_group_id" => 1);

    $executer->insert($data);

    //=============================================

    $data = array("id"   => 1,
                  "name" => "sub_group1",
                  "member_group_id" => 2);

    $executer = new Manipulator("MemberSubGroup");
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

    $executer = new Manipulator("Member");
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

    $executer = new Manipulator($member);
    $executer->save();

    $count = $executer->getCount();
    $this->assertEquals($count, 2);
  }

  public function testSelect()
  {
    $executer = new Manipulator("Member");
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
    $executer = new Manipulator("Member");
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
    $executer = new Manipulator("Member");
    $member1 = $executer->selectOne(1);
    $this->assertTrue($member1->isSelected());

    $executer = new Manipulator("Member");
    $member2 = $executer->selectOne(2);
    $this->assertTrue($member2->isSelected());

    $executer = new Manipulator("Member");
    $member3 = $executer->selectOne(3);
    $this->assertFalse($member3->isSelected());

    $this->assertEquals($member2->id, 2);
    $this->assertEquals($member2->name, "test2");
  }

  public function testUpdate()
  {
    $data = array("is_temp" => false);

    $executer = new Manipulator("Member");
    $executer->setCondition(1);
    $executer->update($data);

    $executer = new Manipulator("Member");

    $threw = false;

    try {
      $executer->update();
    } catch (Exception $e) {
      $threw = true;
    }

    $this->assertTrue($threw);

    $executer = new Manipulator("Member");
    $member1 = $executer->selectOne(1);
    $this->assertFalse($member1->is_temp);

    $executer = new Manipulator("Member");
    $member2 = $executer->selectOne(2);
    $this->assertTrue($member2->is_temp);
  }

  public function testSaveUpdate()
  {
    $executer = new Manipulator("Member");
    $member2 = $executer->selectOne(2);
    $member2->is_temp = false;

    $executer->setModel($member2);
    $executer->save();

    $executer = new Manipulator("Member");
    $member2 = $executer->selectOne(2);
    $this->assertFalse($member2->is_temp);
  }

  public function testParents()
  {
    $executer = new Manipulator("Member");
    $executer->setConstraint("order", "Member.id ASC");
    $join = new Sabel_DB_Join($executer);
    $members = $join->setParents(array("MemberSubGroup"))->join();

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
    $executer = new Manipulator("Member");
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

    $executer = new Manipulator("Member");
    $executer->setConstraint("order", "Member.id ASC");

    $join = new Sabel_DB_Join($executer);
    $relation = new Sabel_DB_Join_Relation(MODEL("MemberSubGroup"));
    $relation->add(MODEL("MemberGroup"));
    $members = $join->add($relation)->join();

    $this->assertEquals(count($members), 2);

    $member1 = $members[0];
    $member2 = $members[1];

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
    $executer = new Manipulator("Member");

    $join = new Sabel_DB_Join($executer);
    $relation = new Sabel_DB_Join_Relation(MODEL("MemberSubGroup"));
    $relation->add(MODEL("MemberGroup"));
    $count = $join->add($relation)->getCount();

    $this->assertEquals($count, 2);

    $executer = new Manipulator("Member");
    $executer->setCondition("MemberGroup.name", "group1");

    $join = new Sabel_DB_Join($executer);
    $relation = new Sabel_DB_Join_Relation(MODEL("MemberSubGroup"));
    $relation->add(MODEL("MemberGroup"));
    $count = $join->add($relation)->getCount();

    $this->assertEquals($count, 1);
  }

  public function testJoinAlias()
  {
    $executer = new Manipulator("Member");
    $executer->setConstraint("order", "Member.id ASC");

    $join = new Sabel_DB_Join($executer);
    $relation = new Sabel_DB_Join_Relation(MODEL("MemberSubGroup"), null, "Msg");
    $relation->add(new Sabel_DB_Join_Object(MODEL("MemberGroup"), null, "MemGrp"));
    $members = $join->add($relation)->join();
    $member1 = $members[0];
    $member2 = $members[1];

    $this->assertEquals($member1->Msg->id, 2);
    $this->assertEquals($member1->Msg->MemGrp->id, 1);
    $this->assertEquals($member1->Msg->MemGrp->name, "group1");
    $this->assertEquals($member2->Msg->id, 1);
    $this->assertEquals($member2->Msg->MemGrp->id, 2);
    $this->assertEquals($member2->Msg->MemGrp->name, "group2");
  }

  public function testJoinAlias2()
  {
    // @todo mail example.
    // use alias for sender, recipient.
  }

  public function testParentParentParent()
  {
    $executer = new Manipulator("Member");
    $executer->setConstraint("order", "Member.id ASC");

    $join = new Sabel_DB_Join($executer);
    $memberGroup = new Sabel_DB_Join_Relation(MODEL("MemberGroup"));
    $memberGroup->add(MODEL("SuperGroup"));
    $memberSubGroup = new Sabel_DB_Join_Relation(MODEL("MemberSubGroup"));
    $memberSubGroup->add($memberGroup);
    $result = $join->add($memberSubGroup)->join();

    $member1 = $result[0];
    $member2 = $result[1];

    $this->assertEquals($member1->id, 1);
    $this->assertEquals($member1->name, "test1");
    $this->assertEquals($member1->MemberSubGroup->id, 2);
    $this->assertEquals($member1->MemberSubGroup->name, "sub_group2");
    $this->assertEquals($member1->MemberSubGroup->MemberGroup->id, 1);
    $this->assertEquals($member1->MemberSubGroup->MemberGroup->name, "group1");
    $this->assertEquals($member1->MemberSubGroup->MemberGroup->SuperGroup->id, 2);
    $this->assertEquals($member1->MemberSubGroup->MemberGroup->SuperGroup->name, "sgroup2");

    $this->assertEquals($member2->id, 2);
    $this->assertEquals($member2->name, "test2");
    $this->assertEquals($member2->MemberSubGroup->id, 1);
    $this->assertEquals($member2->MemberSubGroup->name, "sub_group1");
    $this->assertEquals($member2->MemberSubGroup->MemberGroup->id, 2);
    $this->assertEquals($member2->MemberSubGroup->MemberGroup->name, "group2");
    $this->assertEquals($member2->MemberSubGroup->MemberGroup->SuperGroup->id, 1);
    $this->assertEquals($member2->MemberSubGroup->MemberGroup->SuperGroup->name, "sgroup1");
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

    $executer = new Manipulator("Member");

    foreach ($data as $values) {
      $executer->insert($values);
    }

    $count = $executer->getCount();
    $this->assertEquals($count, 5);
  }

  public function testGetChild()
  {
    $executer = new Manipulator("MemberSubGroup");
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

  public function testValidate()
  {
    $member = MODEL("Member");
    $member->name    = "test6";
    $member->email   = "test6@example.com";
    $member->is_temp = true;
    $member->location_id = 1;
    $member->member_sub_group_id = 1;

    $executer = new Manipulator($member);
    $result = $executer->validate();

    $this->assertEquals(count($result), 1);
    $this->assertEquals($result[0], "please enter a id.");

    //==============================================

    $member = MODEL("Member");
    $member->email   = "test6@example.com";
    $member->is_temp = true;
    $member->location_id = 1;
    $member->member_sub_group_id = 1;

    $executer = new Manipulator($member);
    $result = $executer->validate();

    $this->assertEquals(count($result), 2);

    //==============================================

    $member = MODEL("Member");
    $member->email   = "test6@example.com";
    $member->is_temp = true;
    $member->location_id = 1;
    $member->member_sub_group_id = 1;

    $executer = new Manipulator($member);
    $result = $executer->validate("name");

    $this->assertEquals(count($result), 1);
  }

  public function testConditionTest()
  {
    $data = array("bool_flag" => true);

    $executer = new Manipulator("ConditionTest");
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

    $executer = new Manipulator($model);
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
    $executer = new Manipulator("ConditionTest");
    $models = $executer->select("bool_flag", false);
    $this->assertEquals(count($models), 4);

    $count = $executer->getCount("bool_flag", true);
    $this->assertEquals($count, 2);

    // normal condition.
    $executer = new Manipulator("ConditionTest");
    $model = $executer->selectOne("point", 400);
    $this->assertTrue($model->isSelected());
    $this->assertEquals($model->point, 400);

    $executer = new Manipulator("ConditionTest");
    $executer->setCondition("point", 400);
    $model = $executer->selectOne();
    $this->assertTrue($model->isSelected());
    $this->assertEquals($model->point, 400);

    // and condition.
    $executer = new Manipulator("ConditionTest");
    $executer->setCondition("point", 400);
    $executer->setCondition("name", "name4");
    $model = $executer->selectOne();
    $this->assertTrue($model->isSelected());
    $this->assertEquals($model->point, 400);
    $this->assertEquals($model->name, "name4");

    $executer = new Manipulator("ConditionTest");
    $executer->setCondition("point", 400);
    $executer->setCondition("name", "name5");
    $model = $executer->selectOne();
    $this->assertFalse($model->isSelected());

    // or condition.
    $executer = new Manipulator("ConditionTest");
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
    $executer = new Manipulator("ConditionTest");
    $executer->setCondition(new Sabel_DB_Condition_Object("point", array(200, 400), Sabel_DB_Condition_Object::BETWEEN));
    $executer->setConstraint("order", "point DESC");
    $models = $executer->select();
    $this->assertEquals(count($models), 3);
    $this->assertEquals($models[0]->point, 400);
    $this->assertEquals($models[1]->point, 300);
    $this->assertEquals($models[2]->point, 200);

    // compare condition.
    $executer = new Manipulator("ConditionTest");
    $executer->setCondition(new Sabel_DB_Condition_Object("point", array("<", 400), Sabel_DB_Condition_Object::COMPARE));
    $executer->setConstraint("order", "point DESC");
    $models = $executer->select();
    $this->assertEquals(count($models), 3);
    $this->assertEquals($models[0]->point, 300);
    $this->assertEquals($models[1]->point, 200);
    $this->assertEquals($models[2]->point, 100);

    // or compare condition.
    $executer = new Manipulator("ConditionTest");
    $or = new Sabel_DB_Condition_Or();
    $or->add(new Sabel_DB_Condition_Object("point", array(">=", 400), Sabel_DB_Condition_Object::COMPARE));
    $or->add(new Sabel_DB_Condition_Object("point", array("<=", 200), Sabel_DB_Condition_Object::COMPARE));
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
    $executer = new Manipulator("ConditionTest");
    $executer->setCondition(new Sabel_DB_Condition_Object("name", array("name_", false), Sabel_DB_Condition_Object::LIKE));
    $executer->setConstraint("order", "id ASC");
    $models = $executer->select();
    $this->assertEquals(count($models), 4);
    $this->assertEquals($models[0]->name, "name3");
    $this->assertEquals($models[1]->name, "name4");
    $this->assertEquals($models[2]->name, "name5");
    $this->assertEquals($models[3]->name, "name%");

    $executer = new Manipulator("ConditionTest");
    $executer->setCondition(new Sabel_DB_Condition_Object("name", "name%", Sabel_DB_Condition_Object::LIKE));
    $models = $executer->select();
    $this->assertEquals(count($models), 1);

    $executer = new Manipulator("ConditionTest");
    $executer->setCondition(new Sabel_DB_Condition_Object("name", array("nam%", false), Sabel_DB_Condition_Object::LIKE));
    $executer->setConstraint("order", "id ASC");
    $models = $executer->select();
    $this->assertEquals(count($models), 4);
    $this->assertEquals($models[0]->name, "name3");
    $this->assertEquals($models[1]->name, "name4");
    $this->assertEquals($models[2]->name, "name5");
    $this->assertEquals($models[3]->name, "name%");
  }

  public function testTransaction()
  {
    Sabel_DB_Transaction::activate();

    $data = array();

    $data[] = array("id"      => 6,
                    "name"    => "test6",
                    "email"   => "test6@example.com",
                    "is_temp" => true,
                    "location_id" => 1,
                    "member_sub_group_id" => 2,
                    "updated_at" => "2007-01-01 00:00:00",
                    "created_at" => "2007-01-01 00:00:00");

    $data[] = array("id"      => 7,
                    "name"    => "test7",
                    "email"   => "test7@example.com",
                    "is_temp" => true,
                    "location_id" => 2,
                    "member_sub_group_id" => 1,
                    "updated_at" => "2007-01-01 00:00:00",
                    "created_at" => "2007-01-01 00:00:00");

    $data[] = array("id"      => 8,
                    "name"    => "test8",
                    "email"   => "test8@example.com",
                    "is_temp" => true,
                    "location_id" => 3,
                    "member_sub_group_id" => 1,
                    "updated_at" => "2007-01-01 00:00:00",
                    "created_at" => "2007-01-01 00:00:00");

    $executer = new Manipulator("Member");

    foreach ($data as $values) {
      $executer->insert($values);
    }

    Sabel_DB_Transaction::rollback();

    $count = $executer->getCount();
    $this->assertEquals($count, 5);

    Sabel_DB_Transaction::activate();

    $executer = new Manipulator("Member");

    foreach ($data as $values) {
      $executer->insert($values);
    }

    Sabel_DB_Transaction::commit();

    $count = $executer->getCount();
    $this->assertEquals($count, 8);
  }

  public function testBridge()
  {
    $data = array();
    $data[] = array("id" => 1, "name" => "tanaka");
    $data[] = array("id" => 2, "name" => "yamada");
    $data[] = array("id" => 3, "name" => "satou");
    $data[] = array("id" => 4, "name" => "koike");

    $executer = new Manipulator("Student");
    foreach ($data as $values) {
      $executer->insert($values);
    }

    $data = array();
    $data[] = array("id" => 1, "name" => "science");
    $data[] = array("id" => 2, "name" => "history");
    $data[] = array("id" => 3, "name" => "mathematics");

    $executer = new Manipulator("Course");
    foreach ($data as $values) {
      $executer->insert($values);
    }

    $data = array();
    $data[] = array("student_id" => 1, "course_id" => 2);
    $data[] = array("student_id" => 1, "course_id" => 3);
    $data[] = array("student_id" => 2, "course_id" => 1);
    $data[] = array("student_id" => 2, "course_id" => 3);
    $data[] = array("student_id" => 3, "course_id" => 1);
    $data[] = array("student_id" => 3, "course_id" => 2);
    $data[] = array("student_id" => 3, "course_id" => 3);
    $data[] = array("student_id" => 4, "course_id" => 3);

    $executer = new Manipulator("StudentCourse");
    foreach ($data as $values) {
      $executer->insert($values);
    }

    $executer = new Manipulator("Student");
    $tanaka = $executer->selectOne(1);
    $bridge = new Sabel_DB_Model_Bridge($tanaka, "StudentCourse");
    $course = $bridge->getChild("Course");

    $this->assertEquals(count($course), 2);

    $executer = new Manipulator("Student");
    $yamada = $executer->selectOne(2);
    $bridge = new Sabel_DB_Model_Bridge($yamada, "StudentCourse");
    $course = $bridge->getChild("Course");

    $this->assertEquals(count($course), 2);

    $executer = new Manipulator("Student");
    $satou  = $executer->selectOne(3);
    $bridge = new Sabel_DB_Model_Bridge($satou, "StudentCourse");
    $course = $bridge->getChild("Course");

    $this->assertEquals(count($course), 3);

    $executer = new Manipulator("Course");
    $science = $executer->selectOne(1);
    $bridge  = new Sabel_DB_Model_Bridge($science, "StudentCourse");
    $student = $bridge->getChild("Student");

    $this->assertEquals(count($student), 2);

    $executer = new Manipulator("Course");
    $history = $executer->selectOne(2);
    $bridge  = new Sabel_DB_Model_Bridge($history, "StudentCourse");
    $student = $bridge->getChild("Student");

    $this->assertEquals(count($student), 2);

    $executer = new Manipulator("Course");
    $math    = $executer->selectOne(3);
    $bridge  = new Sabel_DB_Model_Bridge($math, "StudentCourse");
    $student = $bridge->getChild("Student");

    $this->assertEquals(count($student), 4);
  }

  public function testSelfJoin()
  {
    $data   = array();
    $data[] = array("id" => 1, "name" => "root1");
    $data[] = array("id" => 2, "name" => "root2");
    $data[] = array("id" => 3, "name" => "node1", "tree_id" => 2);
    $data[] = array("id" => 4, "name" => "node2", "tree_id" => 2);
    $data[] = array("id" => 5, "name" => "node3", "tree_id" => 1);
    $data[] = array("id" => 6, "name" => "node4", "tree_id" => 1);
    $data[] = array("id" => 7, "name" => "node5", "tree_id" => 1);

    $executer = new Manipulator("Tree");
    foreach ($data as $values) {
      $executer->insert($values);
    }

    if (self::$db === "IBASE") return;

    $executer = new Manipulator("Tree");
    $executer->setConstraint("order", "Tree.id ASC");
    $join = new Sabel_DB_Join($executer);
    $join->add(new Sabel_DB_Join_Object(MODEL("Tree"), null, "Root"));
    $result = $join->join("LEFT");

    $this->assertEquals($result[0]->id, 1);
    $this->assertEquals($result[1]->id, 2);

    $node1 = $result[2];
    $node2 = $result[3];
    $node3 = $result[4];

    $this->assertEquals($node1->name, "node1");
    $this->assertEquals($node1->tree_id, 2);
    $this->assertEquals($node1->Root->id, 2);
    $this->assertEquals($node1->Root->name, "root2");

    $this->assertEquals($node2->name, "node2");
    $this->assertEquals($node2->tree_id, 2);
    $this->assertEquals($node2->Root->id, 2);
    $this->assertEquals($node2->Root->name, "root2");

    $this->assertEquals($node3->name, "node3");
    $this->assertEquals($node3->tree_id, 1);
    $this->assertEquals($node3->Root->id, 1);
    $this->assertEquals($node3->Root->name, "root1");
  }

  public function testSchema()
  {
    $test   = MODEL("SchemaTest");
    $schema = $test->getSchema();
    $id     = $schema->id;
    $name   = $schema->name;
    $bint   = $schema->bint;
    $sint   = $schema->sint;
    $txt    = $schema->txt;
    $bl     = $schema->bl;
    $ft     = $schema->ft;
    $dbl    = $schema->dbl;
    $dt     = $schema->dt;

    $this->assertTrue($id->isInt(true));
    $this->assertTrue($name->isString());
    $this->assertTrue($bint->isBigint());
    $this->assertTrue($sint->isSmallint());
    $this->assertTrue($txt->isText());
    $this->assertTrue($bl->isBool());
    $this->assertTrue($ft->isFloat());
    $this->assertTrue($dbl->isDouble());
    $this->assertTrue($dt->isDate());

    $this->assertEquals($name->default, "hoge");
    $this->assertEquals($name->max, 128);
    $this->assertEquals($bint->default, "90000000000");
    $this->assertEquals($sint->default, 30000);
    $this->assertEquals($ft->default, 1.234);
    $this->assertEquals($dbl->default, 1.23456);

    $this->assertFalse($id->nullable);
    $this->assertFalse($name->nullable);

    $data = array();
    $data[] = array("id" => 1, "name" => "test1", "dt" => "2007-01-01");
    $data[] = array("id" => 2, "name" => "test2", "dt" => "2007-01-02");
    $data[] = array("id" => 3, "name" => "test3", "dt" => "2007-01-03");
    $data[] = array("id" => 4, "name" => "test4", "dt" => "2007-01-04");
    $data[] = array("id" => 5, "name" => "test5", "dt" => "2007-01-05");

    $executer = new Manipulator($test);
    foreach ($data as $values) {
      $executer->insert($values);
    }

    $executer = new Manipulator("SchemaTest");
    $results = $executer->select("dt", "2007-01-03");
    $this->assertEquals(count($results), 1);
    $this->assertEquals($results[0]->id, 3);
    $this->assertEquals($results[0]->name, "test3");
    $this->assertEquals($results[0]->dt, "2007-01-03");

    $executer = new Manipulator("SchemaTest");
    $executer->setCondition(new Sabel_DB_Condition_Object("dt", array("<=", "2007-01-04"), Sabel_DB_Condition_Object::COMPARE));
    $executer->setConstraint("order", "id DESC");
    $results = $executer->select();
    $this->assertEquals(count($results), 4);
    $this->assertEquals($results[0]->dt, "2007-01-04");
    $this->assertEquals($results[1]->dt, "2007-01-03");
    $this->assertEquals($results[2]->dt, "2007-01-02");
    $this->assertEquals($results[3]->dt, "2007-01-01");
  }

  public function testTableList()
  {
    $accessor = new Sabel_DB_Schema_Accessor();
    $tables   = $accessor->getTableList();

    foreach ($tables as $key => $tblName) {
      if ($tblName === "sversion") unset($tables[$key]);
    }

    $this->assertEquals(count($tables), 11);
    $this->assertTrue(in_array("schema_test",  $tables));
    $this->assertTrue(in_array("member_group", $tables));
    $this->assertFalse(in_array("hogehoge",    $tables));
  }

  public function testClear()
  {
    Sabel_DB_Schema::clear();
    Sabel_DB_Connection::closeAll();
  }
}

class Manipulator extends Sabel_DB_Manipulator
{
  const CREATED_TIME_COLUMN = "created_at";
  const UPDATED_TIME_COLUMN = "updated_at";
  const DELETED_TIME_COLUMN = "deleted_at";
  
  public function before($method)
  {
    $method = "before" . ucfirst($method);
    if (method_exists($this, $method)) {
      return $this->$method();
    }
  }
  
  public function after($method, $result)
  {
    if (ENVIRONMENT === DEVELOPMENT) {
      $this->log();
    }
  }
  
  private function beforeValidate()
  {
    $this->setTimestamp();
  }
  
  private function beforeSave()
  {
    $this->setTimestamp();
  }
  
  private function setTimestamp()
  {
    $model    = $this->model;
    $columns  = $model->getColumnNames();
    $datetime = now();
    
    if ($model->{self::UPDATED_TIME_COLUMN} === null) {
      if (in_array(self::UPDATED_TIME_COLUMN, $columns)) {
        $model->{self::UPDATED_TIME_COLUMN} = $datetime;
      }
    }
    
    if (!$model->isSelected() && $model->{self::CREATED_TIME_COLUMN} === null) {
      if (in_array(self::CREATED_TIME_COLUMN, $columns)) {
        $model->{self::CREATED_TIME_COLUMN} = $datetime;
      }
    }
  }

  private function beforeInsert()
  {
    if (!isset($this->arguments[0])) return;
    $columns  = $this->model->getColumnNames();
    $datetime = now();
    
    if (in_array(self::UPDATED_TIME_COLUMN, $columns)) {
      $this->arguments[0][self::UPDATED_TIME_COLUMN] = $datetime;
    }
    
    if (in_array(self::CREATED_TIME_COLUMN, $columns)) {
      $this->arguments[0][self::CREATED_TIME_COLUMN] = $datetime;
    }
  }
  
  private function beforeUpdate()
  {
    if (!isset($this->arguments[0])) return;
    $columns = $this->model->getColumnNames();
    
    if (in_array(self::UPDATED_TIME_COLUMN, $columns)) {
      $this->arguments[0][self::UPDATED_TIME_COLUMN] = now();
    }
  }
  
  private function log()
  {
    static $selectLog = null;
    static $insertLog = null;
    static $updateLog = null;
    static $deleteLog = null;
    static $queryLog  = null;
    
    $stmt = $this->stmt;
    if (is_object($stmt)) {
      if ($stmt->isSelect()) {
        $name = "select";
      } elseif ($stmt->isInsert()) {
        $name = "insert";
      } elseif ($stmt->isUpdate()) {
        $name = "update";
      } elseif ($stmt->isDelete()) {
        $name = "delete";
      } else {
        $name = "query";
      }
      
      $logger = $name . "Log";
      if ($$logger === null) {
        $$logger = new Sabel_Logger_File($name . ".log");
      }
      
      $sql = $stmt->getSql();
      if ($bindParams = $stmt->getBindParams()) {
        $bindParams = $stmt->getDriver()->escape($bindParams);
        $sql = str_replace(array_keys($bindParams), $bindParams, $sql);
      }
      
      $$logger->log($sql);
    }
  }
}
