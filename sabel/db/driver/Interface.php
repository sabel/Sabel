<?php

interface Sabel_DB_Driver_Interface
{
  public function begin($connection);
  public function commit($connection);
  public function rollback($connection);

  public function setBasicSQL($sql);
  public function setUpdateSQL($table, $data);
  public function executeInsert($table, $data, $defColumn);

  public function makeQuery($conditions, $constraints = null);

  public function execute($sql = null, $param = null);
  public function fetch($style = null);
  public function fetchAll($style = null);

  const EITHER = 'OR_';
  const BET    = 'BET_';
  const IN     = 'IN_';
  const LIKE   = 'LIKE_';

  const FETCH_ASSOC = 1;
}
