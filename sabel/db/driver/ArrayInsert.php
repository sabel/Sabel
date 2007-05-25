<?php

class ArrayInsert
{
  public function run($executer)
  {
    $model   = $executer->getModel();
    $tblName = $model->getTableName();
    $array   = $model->getSaveValues();

    $sql = "INSERT INTO $tblName ("
         . implode(", ", array_keys($array[0]))
         . ") VALUES ";

    $driver = $executer->getDriver();
    $vals   = array();

    foreach ($array as $values) {
      $values = $driver->escape($values);
      $vals[] = "(" . implode(", ", $values) . ")";
    }

    $query = $sql . implode(", ", $vals);
    $executer->setResult($driver->setSql($query)->execute());
  }
}

Sabel_DB_Command_Before::regist(array("ArrayInsert", true),
                                Sabel_DB_Command::ARRAY_INSERT,
                                "run",
                                array("driver" => array("include" =>
                                                  array("Sabel_DB_Driver_Mysql"))),
                                Sabel_DB_Command_Before::INTERRUPT);

