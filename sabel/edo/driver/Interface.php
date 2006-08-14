<?php

interface Sabel_Edo_Driver_Interface
{
  public function setBasicSQL($sql);
  public function setUpdateSQL($table, $data);
  public function executeInsert($table, $data, $id_exist = null);

  public function makeQuery($conditions, $constraints = null);

  public function execute($sql = null);
  public function fetch($style = null);
  public function fetchAll($style = null);

  const EITHER = 'OR_';
  const BET    = 'BET_';
  const IN     = 'IN_';
  const LIKE   = 'LIKE_';

  const FETCH_ASSOC = 1;
}
