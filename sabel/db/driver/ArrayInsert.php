<?php

class ArrayInsert
{
  public function createInsertSql($command)
  {
    $model   = $command->getModel();
    $tblName = $model->getTableName();
    $array   = $model->getSaveValues();

    $sql = "INSERT INTO $tblName ("
         . implode(", ", array_keys($array[0]))
         . ") VALUES ";

    $driver = $command->getDriver();
    $vals   = array();

    foreach ($array as $values) {
      $values = $driver->escape($values);
      $vals[] = "(" . implode(", ", $values) . ")";
    }

    $driver->setSql($sql . implode(", ", $vals));
  }
}

Sabel_DB_Command_Before::regist(array("ArrayInsert", true),
                                Sabel_DB_Command::ARRAY_INSERT,
                                array("createInsertSql"),
                                array("driver" =>
                                  array("include" => array("Sabel_DB_Driver_Mysql"))),
                                Sabel_DB_Command_Before::INTERRUPT);

