<?php

class Test_DB_Test extends SabelTestCase
{
  public static $db = "";
  public static $tables = array("member", "member_sub_group", "member_group");

  public function testInsert()
  {
    $data = array("id"   => 1,
                  "name" => "group1");

    $executer = new Sabel_DB_Model_Executer("MemberGroup");
    $executer->insert($data)->execute();

    $data = array("id"   => 2,
                  "name" => "group2");

    $executer->insert($data)->execute();

    $data = array("id"   => 1,
                  "name" => "sub_group1",
                  "member_group_id" => 2);

    $executer = new Sabel_DB_Model_Executer("MemberSubGroup");
    $executer->insert($data)->execute();

    $data = array("id"   => 2,
                  "name" => "sub_group2",
                  "member_group_id" => 1);

    $executer->insert($data)->execute();

    $data = array("id"      => 1,
                  "name"    => "test1",
                  "email"   => "test1@example.com",
                  "is_temp" => true,
                  "member_sub_group_id" => 2);

    $executer = new Sabel_DB_Model_Executer("Member");
    $executer->insert($data)->execute();

    $threw = false;

    try {
      $executer->insert()->execute();
    } catch (Exception $e) {
      $threw = true;
    }

    $this->assertTrue($threw);

    $count = $executer->getCount()->execute();
    $this->assertEquals($count, 1);
  }

  public function testSaveInsert()
  {
    $member = MODEL("Member");

    $member->id      = 2;
    $member->name    = "test2";
    $member->email   = "test2@example.com";
    $member->is_temp = true;
    $member->member_sub_group_id = 1;

    $executer = new Sabel_DB_Model_Executer($member);
    $executer->save()->execute();

    $count = $executer->getCount()->execute();
    $this->assertEquals($count, 2);
  }

  public function testSelect()
  {
    $executer = new Sabel_DB_Model_Executer("Member");
    $executer->setConstraint("order", "id ASC");
    $members  = $executer->select()->execute();

    $this->assertEquals(count($members), 2);

    $member1 = $members[0];
    $member2 = $members[1];

    $this->assertEquals($member1->id, 1);
    $this->assertEquals($member1->name, "test1");
    $this->assertEquals($member1->email, "test1@example.com");
    $this->assertEquals($member1->is_temp, true);

    $this->assertEquals($member2->id, 2);
    $this->assertEquals($member2->name, "test2");
    $this->assertEquals($member2->email, "test2@example.com");
    $this->assertEquals($member2->is_temp, true);

    $executer->setCondition(1);
    $members = $executer->select()->execute();

    $this->assertEquals(count($members), 1);
    $this->assertEquals($members[0]->name, "test1");

    $executer->setCondition(2);
    $members = $executer->select()->execute();

    $this->assertEquals(count($members), 1);
    $this->assertEquals($members[0]->name, "test2");
  }

  public function testSelectOrder()
  {
    $executer = new Sabel_DB_Model_Executer("Member");
    $executer->setConstraint("order", "id DESC");
    $members  = $executer->select()->execute();

    $this->assertEquals(count($members), 2);

    $member1 = $members[0];
    $member2 = $members[1];

    $this->assertEquals($member1->id, 2);
    $this->assertEquals($member1->name, "test2");
    $this->assertEquals($member1->email, "test2@example.com");
    $this->assertEquals($member1->is_temp, true);

    $this->assertEquals($member2->id, 1);
    $this->assertEquals($member2->name, "test1");
    $this->assertEquals($member2->email, "test1@example.com");
    $this->assertEquals($member2->is_temp, true);
  }

  public function testSelectOne()
  {
    $executer = new Sabel_DB_Model_Executer("Member");
    $member1 = $executer->selectOne(1)->execute();
    $this->assertTrue($member1->isSelected());

    $executer = new Sabel_DB_Model_Executer("Member");
    $member2 = $executer->selectOne(2)->execute();
    $this->assertTrue($member2->isSelected());

    $executer = new Sabel_DB_Model_Executer("Member");
    $member3 = $executer->selectOne(3)->execute();
    $this->assertFalse($member3->isSelected());

    $this->assertEquals($member2->id, 2);
    $this->assertEquals($member2->name, "test2");
  }

  public function testUpdate()
  {
    $data = array("is_temp" => false);

    $executer = new Sabel_DB_Model_Executer("Member");
    $executer->setCondition(1);
    $executer->update($data)->execute();

    $executer = new Sabel_DB_Model_Executer("Member");

    $threw = false;

    try {
      $executer->update()->execute();
    } catch (Exception $e) {
      $threw = true;
    }

    $this->assertTrue($threw);

    $executer = new Sabel_DB_Model_Executer("Member");
    $member1 = $executer->selectOne(1)->execute();
    $this->assertFalse($member1->is_temp);

    $executer = new Sabel_DB_Model_Executer("Member");
    $member2 = $executer->selectOne(2)->execute();
    $this->assertTrue($member2->is_temp);
  }

  public function testSaveUpdate()
  {
    $executer = new Sabel_DB_Model_Executer("Member");
    $member2 = $executer->selectOne(2)->execute();
    $member2->is_temp = false;

    $executer->setModel($member2);
    $executer->save()->execute();

    $executer = new Sabel_DB_Model_Executer("Member");
    $member2 = $executer->selectOne(2)->execute();
    $this->assertFalse($member2->is_temp);
  }

  public function testQuery()
  {
    $sql = "SELECT * FROM member ORDER BY id ASC";
    $executer = new Sabel_DB_Model_Executer("Member");
    $result = $executer->query($sql)->execute();
    $this->assertEquals(count($result), 2);

    $member1 = $result[0];
    $member2 = $result[1];

    $this->assertEquals($member1->name, "test1");
    $this->assertEquals($member2->name, "test2");

    $sql = "SELECT * FROM member WHERE name = %s ORDER BY id ASC";
    $executer = new Sabel_DB_Model_Executer("Member");
    $result = $executer->query($sql, array("test2"))->execute();
    $this->assertEquals(count($result), 1);

    $this->assertEquals($result[0]->name, "test2");

    $executer = new Sabel_DB_Model_Executer("Member");
    $result = $executer->query($sql, array("test2"), true)->execute();
    $this->assertEquals(count($result), 1);

    $this->assertTrue(is_array($result[0]));
    $this->assertEquals($result[0]["name"], "test2");
  }

  public function testParents()
  {
    $executer = new Sabel_DB_Model_Executer("Member");
    $executer->setParents(array("MemberSubGroup"));
    $executer->setConstraint("order", "Member.id ASC");
    $members = $executer->select()->execute();

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
    $executer = new Sabel_DB_Model_Executer("Member");
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

    $executer = new Sabel_DB_Model_Executer("Member");
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
    $executer = new Sabel_DB_Model_Executer("Member");

    $join = new Sabel_DB_Join($executer);
    $relation = new Sabel_DB_Join_Relation(MODEL("MemberSubGroup"));
    $relation->add(MODEL("MemberGroup"));
    $count = $join->add($relation)->getCount();

    $this->assertEquals($count, 2);

    $executer = new Sabel_DB_Model_Executer("Member");
    $executer->setCondition("MemberGroup.name", "group1");

    $join = new Sabel_DB_Join($executer);
    $relation = new Sabel_DB_Join_Relation(MODEL("MemberSubGroup"));
    $relation->add(MODEL("MemberGroup"));
    $count = $join->add($relation)->getCount();

    $this->assertEquals($count, 1);
  }

  public function testArrayInsert()
  {
    $data = array();

    $data[] = array("id"      => 3,
                    "name"    => "test3",
                    "email"   => "test3@example.com",
                    "is_temp" => true,
                    "member_sub_group_id" => 2);

    $data[] = array("id"      => 4,
                    "name"    => "test4",
                    "email"   => "test4@example.com",
                    "is_temp" => true,
                    "member_sub_group_id" => 1);

    $data[] = array("id"      => 5,
                    "name"    => "test5",
                    "email"   => "test5@example.com",
                    "is_temp" => true,
                    "member_sub_group_id" => 1);

    $executer = new Sabel_DB_Model_Executer("Member");
    $executer->arrayInsert($data)->execute();

    $count = $executer->getCount()->execute();
    $this->assertEquals($count, 5);
  }
}

class Proxy extends Sabel_DB_Model
{
  public function __construct($mdlName)
  {
    $this->initialize($mdlName);
  }
}
