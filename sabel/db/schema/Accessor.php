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

  public function getColumnNames($tblName)
  {
    $schemaClass = 'Schema_' . join('', array_map('ucfirst', explode('_', $tblName)));

    if (class_exists($schemaClass, false)) {
      $sc   = new $schemaClass();
      $cols = $sc->get();
    } else {
      $executer = new Sabel_DB_Executer($this->connectName);
      $executer->setConstraint('limit', 1);
      $executer->getStatement()->setBasicSQL("SELECT * FROM $tblName");
      $cols = $executer->execute()->fetch();
    }
    return array_keys($cols);
  }

  /**
   *  for mysql.
   *
   */
  public function getTableEngine($tblName, $driver = null)
  {
    $schemaClass = 'Schema_' . join('', array_map('ucfirst', explode('_', $tblName)));

    if (class_exists($schemaClass, false)) {
      $sc = new $schemaClass();
      return $sc->getEngine();
    }

    if (is_null($driver)) {
      $driver = Sabel_DB_Connection::getDriver($this->connectName);
    }
    $driver->execute("SHOW TABLE STATUS WHERE Name='{$tblName}'");
    $row = $driver->getResultSet()->fetch();
    return $row['Engine'];
  }
}
