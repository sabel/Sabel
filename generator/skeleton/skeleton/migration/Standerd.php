<#php

class <? echo ucfirst($name) ?> extends Sabel_DB_Migration
{
  public function upgrade()
  {
    $table = $this->table("<? echo ucfirst($name) ?>");
    $table->addColumn("id", $int, array("attributes" => array("auto_increment",
                                                              "NOT NULL",
                                                              "PRIMARY KEY")));
    $table->create();
    
    return "create table <? echo ucfirst($name) ?>";
  }
  
  public function downgrade()
  {
    $table = $this->table("<? echo ucfirst($name) ?>");
    $table->dropTable();
    return "drop table <? echo ucfirst($name) ?>";
  }
}