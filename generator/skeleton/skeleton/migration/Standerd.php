<#php

class <?php echo ucfirst($className) ?><?php echo $nextVersion ?> extends Sabel_DB_Migration
{
  protected $table       = "<?php echo $table ?>";
  protected $connectName = "default";
  
  public function upgrade()
  {
    $this->add("id TYPE::BINT(INCREMENT) PRIMARY KEY");
    return "create table <?php echo $table ?>";
  }
  
  public function downgrade()
  {
    $this->delete();
    return "drop table <?php echo $table ?>";
  }
}