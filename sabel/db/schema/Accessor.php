<?php

/**
 * Sabel_DB_Schema_Accessor
 *
 * @package org.sabel.db.schema
 * @author Ebine Yutaka <ebine.yutaka@gamil.com>
 */
class Sabel_DB_Schema_Accessor
{
  private $connectName = '';
  private $schemaClass = null;

  public function __construct($connectName, $schema)
  {
    $dbName    = ucfirst(Sabel_DB_Connection::getDB($connectName));
    $className = 'Sabel_DB_Schema_' . $dbName;

    $this->schemaClass = new $className($connectName, $schema);
    $this->connectName = $connectName;
  }

  public function getTables()
  {
    return $this->schemaClass->getTables();
  }

  public function getTable($tblName)
  {
    return $this->schemaClass->getTable($tblName);
  }

  public function getTableNames()
  {
    $schemaClass = 'Schema_' . ucfirst($this->connectName) . 'TableList';

    if (class_exists($schemaClass, false)) {
      $sc = new $schemaClass();
      return $sc->get();
    } else {
      return $this->schemaClass->getTableNames();
    }
  }

  public function getColumnNames($table)
  {
    $schemaClass = 'Schema_' . join('', array_map('ucfirst', explode('_', $table)));

    if (class_exists($schemaClass, false)) {
      $sc   = new $schemaClass();
      $cols = $sc->get();
    } else {
      $executer = new Sabel_DB_Executer($this->connectName);
      $executer->setConstraint('limit', 1);
      $executer->getStatement()->setBasicSQL("SELECT * FROM $table");

      $resultSet = $executer->execute();
      $cols = $resultSet->fetch();
    }
    return array_keys($cols);
  }
}

/**
 * public function. schema()
 *
 */
function schema($model)
{
  if ($model instanceof Sabel_DB_Mapper) {
    $sa = new Sabel_DB_Schema_Accessor($model->getConnectName(), $model->getSchemaName());
    $columns = $sa->getTable($model->getTableName())->getColumns();

    $data = $model->toArray();
    foreach ($data as $key => $val) {
      if (array_key_exists($key, $columns)) $columns[$key]->value = $val;
    }
    return $columns;
  } else {
    throw new Exception('Error: argument should be an instance of Sabel_DB_Mapper');
  }
}
