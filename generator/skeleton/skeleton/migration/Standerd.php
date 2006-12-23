<#php

class <?php echo ucfirst($className) ?><?php echo $nextVersion ?> extends Sabel_DB_Migration
{
  public function upgrade()
  {
    $this->add(Migration::TABLE, "<?php echo $table ?>", "id TYPE::BINT(INCREMENT) PRIMARY KEY");
    return "create table <?php echo $table ?>";
  }
  
  public function downgrade()
  {
    $this->delete(Migration::TABLE, "<?php echo $table ?>");
    return "drop table <?php echo $table ?>";
  }
}
