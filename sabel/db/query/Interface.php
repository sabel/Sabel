<?php

interface Sabel_DB_Query_Interface
{
  public function getSQL();
  public function setBasicSQL($sql);

  public function makeNormalSQL($key, $val);
  public function makeIsNullSQL($key);
  public function makeIsNotNullSQL($key);

  public function makeWhereInSQL($key, $val);
  public function makeLikeSQL($key, $val, $esc = null);
  public function makeBetweenSQL($key, $val);
  public function makeEitherSQL($key, $val);
  public function makeLess_GreaterSQL($key, $val);

  public function makeConditionQuery($conditions);
  public function makeConstraintQuery($constraints);
}
