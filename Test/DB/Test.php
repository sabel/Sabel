<?php

/**
 * sabel.db functional test(using databases).
 *
 * @category  DB
 * @author    Ebine Yutaka <ebine.yutaka@gmail.com>
 */
class Test_DB_Test extends SabelTestCase
{
  public static $db = "";
  public static $tables = array("schema_test", "grandchildren", "children",
                                "parents", "grandparents", "student_course", "student", "course");
                                
  protected static $lastStId = null;
  
  public function testClean()
  {
    $tables = self::$tables;
    $driver = Sabel_DB_Driver::create();

    foreach ($tables as $table) {
      $driver->execute("DELETE FROM $table");
    }
  }
  
  public function testInsert()
  {
    $st = MODEL("SchemaTest");
    $st->email = "test1@example.com";
    $st->bl = true;
    $generatedId = $st->insert();
    
    $this->assertTrue(is_numeric($generatedId));
  }
  
  public function testInsertBySave()
  {
    $st = MODEL("SchemaTest");
    $st->email = "test2@example.com";
    $st->bl = false;
    
    $saved = $st->save();
    
    // default values.
    $this->assertEquals("default name", $saved->name);
    $this->assertEquals("90000000000",  $saved->bint);
    $this->assertEquals(30000,    $saved->sint);
    $this->assertEquals(10.234,   $saved->ft);
    $this->assertEquals(10.23456, $saved->dbl);
    
    self::$lastStId = $saved->id;
  }
  
  public function testSelectOne()
  {
    $st = MODEL("SchemaTest");
    $st = $st->selectOne(self::$lastStId);
    
    $this->assertTrue($st->isSelected());
    $this->assertEquals("test2@example.com", $st->email);
    $this->assertEquals(false, $st->bl);
  }
  
  public function testInitSelect()
  {
    $st = MODEL("SchemaTest", self::$lastStId);
    $this->assertTrue($st->isSelected());
    $this->assertEquals("test2@example.com", $st->email);
    $this->assertEquals(false, $st->bl);
  }
  
  public function testUpdate()
  {
    $st = MODEL("SchemaTest");
    $st->setCondition(self::$lastStId);
    $st->update(array("bl" => true));
    
    $st = MODEL("SchemaTest", self::$lastStId);
    $this->assertEquals("test2@example.com", $st->email);
    $this->assertEquals(true, $st->bl);
  }
  
  public function testUpdateBySave()
  {
    $st = MODEL("SchemaTest", self::$lastStId);
    $st->email = "test2@updated.com";
    $st->bl = false;
    $st->save();
    
    $st = MODEL("SchemaTest", self::$lastStId);
    $this->assertEquals("test2@updated.com", $st->email);
    $this->assertEquals(false, $st->bl);
  }
  
  public function testCount()
  {
    $st = MODEL("SchemaTest");
    $this->assertEquals(2, $st->getCount());
    
    $st->setCondition(self::$lastStId);
    $this->assertEquals(1, $st->getCount());
    
    $this->assertEquals(1, $st->getCount("email", "test2@updated.com"));
  }
  
  public function testDelete()
  {
    $st = MODEL("SchemaTest");
    $st->setCondition(self::$lastStId);
    $st->delete();
    
    $this->assertEquals(1, $st->getCount());
    $st->delete("email", "test1@example.com");
    $this->assertEquals(0, $st->getCount());
    
    $this->insertTestData();
  }
  
  public function testJoin1()
  {
    $this->insertJoinTableData();
    $join = new Sabel_DB_Join("Grandchildren");
    $join->setOrderBy("Grandchildren.id ASC");
    $results = $join->add("Children")->join();
    
    $this->assertEquals(2, count($results));
    $this->assertEquals("grandchildren1", $results[0]->value);
    $this->assertEquals("grandchildren2", $results[1]->value);
    $this->assertEquals("children2", $results[0]->Children->value);
    $this->assertEquals("children1", $results[1]->Children->value);
  }
  
  public function testJoin2()
  {
    $join = new Sabel_DB_Join("Grandchildren");
    $join->setOrderBy("Grandchildren.id ASC");
    $chilren = new Sabel_DB_Join_Relation("Children");
    $results = $join->add($chilren->add("Parents"))->join();
    
    $this->assertEquals(2, count($results));
    $this->assertEquals("children2", $results[0]->Children->value);
    $this->assertEquals("children1", $results[1]->Children->value);
    $this->assertEquals("parents1", $results[0]->Children->Parents->value);
    $this->assertEquals("parents2", $results[1]->Children->Parents->value);
  }
  
  public function testJoin3()
  {
    $join = new Sabel_DB_Join("Grandchildren");
    $join->setOrderBy("Grandchildren.id ASC");
    $children = new Sabel_DB_Join_Relation("Children");
    $parents = new Sabel_DB_Join_Relation("Parents");
    $results = $join->add($children->add($parents->add("Grandparents")))->join();
    
    $this->assertEquals(2, count($results));
    $this->assertEquals("children2", $results[0]->Children->value);
    $this->assertEquals("children1", $results[1]->Children->value);
    $this->assertEquals("parents1", $results[0]->Children->Parents->value);
    $this->assertEquals("parents2", $results[1]->Children->Parents->value);
    $this->assertEquals("grandparents2", $results[0]->Children->Parents->Grandparents->value);
    $this->assertEquals("grandparents1", $results[1]->Children->Parents->Grandparents->value);
  }
  
  public function testJoinCondition()
  {
    $join = new Sabel_DB_Join("Grandchildren");
    $join->setCondition("Grandparents.value", "grandparents2");
    $children = new Sabel_DB_Join_Relation("Children");
    $parents = new Sabel_DB_Join_Relation("Parents");
    
    $results = $join->add($children->add($parents->add("Grandparents")))->join();
    $this->assertEquals(1, count($results));
    $this->assertEquals("children2", $results[0]->Children->value);
    $this->assertEquals("parents1", $results[0]->Children->Parents->value);
    $this->assertEquals("grandparents2", $results[0]->Children->Parents->Grandparents->value);
  }
  
  public function testEqualCondition()
  {
    $st = MODEL("SchemaTest");
    $results = $st->select("sint", 200);
    $this->assertEquals(2, count($results));
    
    $results = $st->select(Condition::create(EQUAL, "sint", 200));
    $this->assertEquals(2, count($results));
    
    $st->setCondition("sint", 100);
    $st->setCondition("name", "name2");
    $results = $st->select();
    $this->assertEquals(1, count($results));
    $this->assertEquals("test2@example.com", $results[0]->email);
    
    $st->setCondition("sint", 100);
    $st->setCondition("name", "name5");
    $this->assertEquals(0, count($st->select()));
  }
  
  public function testBetweenCondition()
  {
    $st = MODEL("SchemaTest");
    $between = array("2008-01-06", "2008-01-10");
    $results = $st->select(Condition::create(BETWEEN, "dt", $between));
    $this->assertEquals(5, count($results));
    
    $st->setCondition("bl", true);
    $st->setCondition(Condition::create(BETWEEN, "dt", $between));
    $this->assertEquals(4, count($st->select()));
  }
  
  public function testGreaterCondition()
  {
    $st = MODEL("SchemaTest");
    $results = $st->select(Condition::create(GREATER_THAN, "sint", 300));
    $this->assertEquals(4, count($results));
    
    $results = $st->select(Condition::create(GREATER_EQUAL, "sint", 300));
    $this->assertEquals(6, count($results));
    
    $st->setCondition(Condition::create(GREATER_EQUAL, "sint", 300));
    $st->setCondition("bl", false);
    $this->assertEquals(1, count($st->select()));
  }
  
  public function testLessCondition()
  {
    $st = MODEL("SchemaTest");
    $results = $st->select(Condition::create(LESS_THAN, "sint", 300));
    $this->assertEquals(4, count($results));
    
    $results = $st->select(Condition::create(LESS_EQUAL, "sint", 300));
    $this->assertEquals(6, count($results));
    
    $st->setCondition(Condition::create(LESS_EQUAL, "sint", 300));
    $st->setCondition("bl", false);
    $this->assertEquals(3, count($st->select()));
  }
  
  public function testIsNullCondition()
  {
    $st = MODEL("SchemaTest");
    $results = $st->select(Condition::create(ISNULL, "txt"));
    $this->assertEquals(8, count($results));
    
    $this->assertEquals(4, count($st->select("bl", false)));
    
    $st->setCondition(Condition::create(ISNULL, "txt"));
    $st->setCondition("bl", false);
    $this->assertEquals(3, count($st->select()));
  }
  
  public function testIsNotNullCondition()
  {
    $st = MODEL("SchemaTest");
    $results = $st->select(Condition::create(ISNOTNULL, "txt"));
    $this->assertEquals(2, count($results));
  }
  
  public function testLikeCondition()
  {
    $st = MODEL("SchemaTest");
    $results = $st->select(Condition::create(LIKE, "name", "name"));
    $this->assertEquals(10, count($results));
    
    $like = Condition::create(LIKE, "name", "name_")->escape(false)->type(LIKE_FIXED);
    $this->assertEquals(9, count($st->select($like)));
    
    $like = Condition::create(LIKE, "name", "name_")->type(LIKE_FIXED);
    $this->assertEquals(1, count($st->select($like)));
    
    $like = Condition::create(LIKE, "name", "0")->type(LIKE_ENDS_WITH);
    $this->assertEquals(1, count($st->select($like)));
    
    $like = Condition::create(LIKE, "name", "a")->type(LIKE_ENDS_WITH);
    $this->assertEquals(0, count($st->select($like)));
  }
  
  public function testOrderBy()
  {
    $st = MODEL("SchemaTest");
    $results = $st->setOrderBy("dt")->select();
    $this->assertEquals("2008-01-01", $results[0]->dt);
    $this->assertEquals("2008-01-10", $results[9]->dt);
    
    $results = $st->setOrderBy("dt DESC")->select();
    $this->assertEquals("2008-01-10", $results[0]->dt);
    $this->assertEquals("2008-01-01", $results[9]->dt);
    
    $results = $st->setOrderBy("SchemaTest.dt DESC")->select();
    $this->assertEquals("2008-01-10", $results[0]->dt);
    $this->assertEquals("2008-01-01", $results[9]->dt);
  }
  
  public function testLimitOffset()
  {
    $st = MODEL("SchemaTest");
    $st->setLimit(2)->setOrderBy("id DESC");
    $results = $st->select();
    $this->assertEquals(2, count($results));
    $this->assertEquals("name10", $results[0]->name);
    $this->assertEquals("name_", $results[1]->name);
    
    $st->setLimit(2)->setOffset(2)->setOrderBy("id DESC");
    $results = $st->select();
    $this->assertEquals(2, count($results));
    $this->assertEquals("name8", $results[0]->name);
    $this->assertEquals("name7", $results[1]->name);
    
    $st->setLimit(2)->setOffset(4)->setOrderBy("id DESC");
    $results = $st->select();
    $this->assertEquals(2, count($results));
    $this->assertEquals("name6", $results[0]->name);
    $this->assertEquals("name5", $results[1]->name);
  }
  
  public function testSelectByQuery()
  {
    $st = MODEL("SchemaTest");
    $results = $st->selectByQuery("WHERE sint = 100");
    $this->assertEquals(2, count($results));
    
    $results = $st->selectByQuery("WHERE sint = @param@", array("param" => 100));
    $this->assertEquals(2, count($results));
    
    $results = $st->selectByQuery("ORDER BY schema_test.dt DESC");
    $this->assertEquals("2008-01-10", $results[0]->dt);
    $this->assertEquals("2008-01-01", $results[9]->dt);
    
    $st->setProjection(array("sint"));
    $results = $st->selectByQuery("GROUP BY sint");
    $this->assertEquals(5, count($results));
  }
  
  public function testModelCondition()
  {
    $child = MODEL("Children")->selectOne(1);
    $gChildren = MODEL("Grandchildren")->select($child);
    $this->assertEquals(1, count($gChildren));
    
    $gc = $gChildren[0];
    $this->assertEquals(2, $gc->id);
    $this->assertEquals(1, $gc->children_id);
    $this->assertEquals("grandchildren2", $gc->value);
  }
  
  public function testRollback()
  {
    Sabel_DB_Transaction::activate(Sabel_DB_Transaction::SERIALIZABLE);
    
    $gp = MODEL("Grandparents");
    $gp->insert(array("id" => 3, "value" => "grandparents3"));
    $gp->insert(array("id" => 4, "value" => "grandparents4"));
    
    Sabel_DB_Transaction::rollback();
    $this->assertEquals(2, $gp->getCount());
  }
  
  public function testCommit()
  {
    Sabel_DB_Transaction::activate(Sabel_DB_Transaction::READ_COMMITTED);
    
    $gp = MODEL("Grandparents");
    $gp->insert(array("id" => 3, "value" => "grandparents3"));
    $gp->insert(array("id" => 4, "value" => "grandparents4"));
    
    Sabel_DB_Transaction::commit();
    $this->assertEquals(4, $gp->getCount());
  }
  
  /**
   * information(schema) of table
   */
  public function testTableInfo()
  {
    $schema = MODEL("SchemaTest")->getSchema();
    $this->assertEquals("schema_test", $schema->getTableName());
    $this->assertEquals("id", $schema->getPrimaryKey());
    $this->assertEquals("id", $schema->getSequenceColumn());
    
    $this->assertTrue($schema->id->isInt(true));
    $this->assertTrue($schema->id->primary);
    $this->assertTrue($schema->id->increment);
    $this->assertFalse($schema->name->primary);
    $this->assertFalse($schema->name->increment);
    
    $this->assertTrue($schema->bint->isBigint());
    $this->assertTrue($schema->sint->isSmallint());
    $this->assertTrue($schema->bint->isInt());
    $this->assertTrue($schema->sint->isInt());
    $this->assertFalse($schema->bint->isInt(true));  // strict mode
    $this->assertFalse($schema->sint->isInt(true));  // strict mode
    
    $this->assertTrue($schema->name->isString());
    $this->assertEquals(128, $schema->name->max);
    $this->assertTrue($schema->email->isString());
    $this->assertEquals(255, $schema->email->max);
    
    $this->assertTrue($schema->bl->isBool());
    $this->assertEquals(false, $schema->bl->default);
    $this->assertTrue($schema->ft->isFloat());
    $this->assertEquals(10.234, $schema->ft->default);
    $this->assertTrue($schema->dbl->isDouble());
    $this->assertEquals(10.23456, $schema->dbl->default);
    $this->assertTrue($schema->txt->isText());
    $this->assertTrue($schema->dt->isDate());
    
    $uniques = $schema->getUniques();
    $this->assertTrue(is_array($uniques));
    $this->assertEquals(1, count($uniques));
    $this->assertEquals(1, count($uniques[0]));
    $this->assertEquals("email", $uniques[0][0]);
    $this->assertTrue($schema->isUnique("email"));
  }
  
  public function testBridge()
  {
    $join = new Sabel_DB_Join("StudentCourse");
    $join->setOrderBy("StudentCourse.student_id ASC, StudentCourse.course_id ASC");
    $r = $join->setParents(array("Student", "Course"))->join();
    
    $this->assertEquals(7, count($r));
    $this->assertEquals("yamada",      $r[0]->Student->name);
    $this->assertEquals("Mathematics", $r[0]->Course->name);
    $this->assertEquals("yamada",      $r[1]->Student->name);
    $this->assertEquals("Physics",     $r[1]->Course->name);
    $this->assertEquals("tanaka",      $r[2]->Student->name);
    $this->assertEquals("Mathematics", $r[2]->Course->name);
    $this->assertEquals("tanaka",      $r[3]->Student->name);
    $this->assertEquals("Science",     $r[3]->Course->name);
    $this->assertEquals("suzuki",      $r[4]->Student->name);
    $this->assertEquals("Mathematics", $r[4]->Course->name);
    $this->assertEquals("suzuki",      $r[5]->Student->name);
    $this->assertEquals("Physics",     $r[5]->Course->name);
    $this->assertEquals("suzuki",      $r[6]->Student->name);
    $this->assertEquals("Science",     $r[6]->Course->name);
  }
  
  public function testBridgeWithCondition()
  {
    $join = new Sabel_DB_Join("StudentCourse");
    $join->setOrderBy("StudentCourse.student_id ASC, StudentCourse.course_id ASC");
    $join->setCondition("Student.id", 1);
    $r = $join->setParents(array("Student", "Course"))->join();
    
    $this->assertEquals(2, count($r));
    $this->assertEquals("yamada",      $r[0]->Student->name);
    $this->assertEquals("Mathematics", $r[0]->Course->name);
    $this->assertEquals("yamada",      $r[1]->Student->name);
    $this->assertEquals("Physics",     $r[1]->Course->name);
  }
  
  public function testBridgeCount()
  {
    $join = new Sabel_DB_Join("StudentCourse");
    $join->setCondition("Student.id", 3);
    $this->assertEquals(3, $join->setParents(array("Student", "Course"))->getCount());
  }
  
  // @todo more tests
  
  public function testClear()
  {
    Sabel_DB_Schema::clear();
    Sabel_DB_Connection::closeAll();
  }
  
  protected function insertTestData()
  {
    $data = array();
    $data[] = array("name" => "name1", "email" => "test1@example.com", "sint" => 100, "bl" => false, "ft" => 1.234, "dt" => "2008-01-01");
    $data[] = array("name" => "name2", "email" => "test2@example.com", "sint" => 100, "bl" => false, "ft" => 2.234, "dt" => "2008-01-02");
    $data[] = array("name" => "name3", "email" => "test3@example.com", "sint" => 200, "bl" => true, "ft" => 3.234, "dt" => "2008-01-03");
    $data[] = array("name" => "name4", "email" => "test4@example.com", "sint" => 200, "bl" => false, "ft" => 4.234, "txt" => "body", "dt" => "2008-01-04");
    $data[] = array("name" => "name5", "email" => "test5@example.com", "sint" => 300, "bl" => true, "ft" => 5.234, "dt" => "2008-01-05");
    $data[] = array("name" => "name6", "email" => "test6@example.com", "sint" => 300, "bl" => true, "ft" => 6.234, "txt" => "body", "dt" => "2008-01-06");
    $data[] = array("name" => "name7", "email" => "test7@example.com", "sint" => 400, "bl" => false, "ft" => 7.234, "dt" => "2008-01-07");
    $data[] = array("name" => "name8", "email" => "test8@example.com", "sint" => 400, "bl" => true, "ft" => 8.234, "dt" => "2008-01-08");
    $data[] = array("name" => "name_", "email" => "test9@example.com", "sint" => 500, "bl" => true, "ft" => 9.234, "dt" => "2008-01-09");
    $data[] = array("name" => "name10", "email" => "test10@example.com", "sint" => 500, "bl" => true, "ft" => 10.234, "dt" => "2008-01-10");
    
    $st = MODEL("SchemaTest");
    foreach ($data as $values) $st->insert($values);
    
    $data = array();
    $data[] = array("id" => 1, "name" => "yamada");
    $data[] = array("id" => 2, "name" => "tanaka");
    $data[] = array("id" => 3, "name" => "suzuki");
    
    $student = MODEL("Student");
    foreach ($data as $values) $student->insert($values);
    
    $data = array();
    $data[] = array("id" => 1, "name" => "Mathematics");
    $data[] = array("id" => 2, "name" => "Physics");
    $data[] = array("id" => 3, "name" => "Science");
    
    $course = MODEL("Course");
    foreach ($data as $values) $course->insert($values);
    
    $data = array();
    $data[] = array("student_id" => 1, "course_id" => 1,"val" => "val1");
    $data[] = array("student_id" => 1, "course_id" => 2,"val" => "val2");
    $data[] = array("student_id" => 2, "course_id" => 1,"val" => "val3");
    $data[] = array("student_id" => 2, "course_id" => 3,"val" => "val4");
    $data[] = array("student_id" => 3, "course_id" => 1,"val" => "val5");
    $data[] = array("student_id" => 3, "course_id" => 2,"val" => "val6");
    $data[] = array("student_id" => 3, "course_id" => 3,"val" => "val7");
    
    $sc = MODEL("StudentCourse");
    foreach ($data as $values) $sc->insert($values);
  }
  
  protected function insertJoinTableData()
  {
    $data = array();
    $data[] = array("id" => 1, "value" => "grandparents1");
    $data[] = array("id" => 2, "value" => "grandparents2");
    $gp = MODEL("Grandparents");
    foreach ($data as $values) $gp->insert($values);
    
    $data = array();
    $data[] = array("id" => 1, "grandparents_id" => 2, "value" => "parents1");
    $data[] = array("id" => 2, "grandparents_id" => 1, "value" => "parents2");
    $p = MODEL("Parents");
    foreach ($data as $values) $p->insert($values);
    
    $data = array();
    $data[] = array("id" => 1, "parents_id" => 2, "value" => "children1");
    $data[] = array("id" => 2, "parents_id" => 1, "value" => "children2");
    $c = MODEL("Children");
    foreach ($data as $values) $c->insert($values);
    
    $data = array();
    $data[] = array("id" => 1, "children_id" => 2, "value" => "grandchildren1");
    $data[] = array("id" => 2, "children_id" => 1, "value" => "grandchildren2");
    $gc = MODEL("Grandchildren");
    foreach ($data as $values) $gc->insert($values);
  }
}
