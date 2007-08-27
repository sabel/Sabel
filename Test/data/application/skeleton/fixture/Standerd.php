<#php

class Fixture_<? echo ucfirst($name) ?>

{
  public function drop()
  {
    $db = new Sabel_DB_Basic();
    $db->setDriver('default');
    $sql = "DROP TABLE <? echo $name ?>";
    $db->execute($sql);
  }
  
  public function create()
  {
    $db = new Sabel_DB_Basic();
    $db->setDriver('default');
    $sql = "
           CREATE TABLE <? echo $name ?> (
             id INT PRIMARY KEY AUTO_INCREMENT
           )
           ";
    $db->execute($sql);
  }
}