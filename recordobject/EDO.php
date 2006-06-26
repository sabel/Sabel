<?php

interface EDO
{
  public function setBasicSQL($sql);
  public function setUpdateSQL($table, $data);
  public function setInsertSQL($table, $data);

  public function makeQuery(&$conditions = null, &$constraints = null);

  public function execute($sql = null);
  public function fetch($style = null, $cursor = null, $offset = null);

  const BETWEEN_SEP = '%B';
  const EITHER_SEP  = '%O';

  const FETCH_ASSOC = 1;
}

?>
