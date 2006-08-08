<?php

require_once('PHPUnit2/Framework/TestCase.php');

class Test_InformationSchema extends PHPUnit2_Framework_TestCase
{
  public function testPrototype()
  {
    $is = new Edo_InformationSchema();
    $userTable = $is->getTableByName('users');
    
    $tables = $is->getTables();
    foreach ($tables as $table) {
      $this->assertEquals(Edo_Type::INT, $table->getAutoIncrementColummn()->type);
      $this->assertEquals(Edo_Type::INT, $table->getColumnByBame('id')->type);
      $this->assertEquals('id', $table->getAutoIncrementColummn()->name);
      
      foreach ($table->getColumns() as $column) {
        if ($column->name == 'id') {
          $this->assertTrue($column->isAutoIncrement());
        }
        
        $this->assertEquals(Edo_Type::VARCHAR, $column->type);
        
        if ($column->name == 'age' && $column->hasDefaultValue()) {
          $this->assertEquals(0, $column->getDefaultValue());
        }
        
        if ($column->type == Edo_Type::STRING) {
          $this->assertTrue(is_int($column->length));
        }
        
        if ($column->type == Edo_Type::INT) {
          $this->assertTrue(is_int($column->min));
          $this->assertTrue(is_int($column->max));
        }
        
        // @todo how to use blob type.
      }
    }
  }
}
