<?php

interface Query
{
  public function getSQL();
  public function setBasicSQL($sql);

  public function makeNormalConditionSQL($key, $val);
  public function makeIsNullSQL($key, $val = null);
  public function makeIsNotNullSQL($key, $val = null);
  public function makeWhereInSQL($key, $val);
  public function makeLikeSQL($key, $val);
  public function makeBetweenSQL($key, $val, $sep);
  public function makeEitherSQL($key, $val, $sep);
  public function makeLess_GreaterSQL($key, $val, $sep);

  public function makeConstraintSQL($constraints);
}

?>
