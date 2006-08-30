<?php

/**
 * Sabel_DB_Schema_Accessor
 *
 * @package org.sabel.db.schema
 * @author Ebine Yutaka <ebine.yutaka@gamil.com>
 */
class Sabel_DB_Schema_Accessor
{
  protected $is = null;

  public function __construct($connectName, $schema)
  {
    $dbName    = ucfirst(Sabel_DB_Connection::getDB($connectName));
    $className = "Sabel_DB_Schema_{$dbName}";
    $this->is  = new $className($connectName, $schema);
  }

  public function getTables()
  {
    return $this->is->getTables();
  }

  public function getTable($name)
  {
    return $this->is->getTable($name);
  }

  public function getColumn($table, $column)
  {
    return $this->is->createColumn($table, $column);
  }
}
