<?php

interface Sabel_DB_Query
{
  public function getSQL();
  public function setBasicSQL($sql);

  public function makeNormalConditionSQL($key, $val);
  public function makeIsNullSQL($key);
  public function makeIsNotNullSQL($key);
  public function makeWhereInSQL($key, $val);
  public function makeLikeSQL($key, $val);
  public function makeBetweenSQL($key, $val);
  public function makeEitherSQL($key, $val);
  public function makeLess_GreaterSQL($key, $val);

  public function makeConstraintSQL($constraints);
}
