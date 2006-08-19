<?php

class Sabel_DB_Query_Factory
{
  public function makeConditionQuery($conditions)
  {
    if (!$conditions) return true;

    $check = true;
    foreach ($conditions as $key => $val) {
      if ($val[0] == '>' || $val[0] == '<') {
        $this->makeLess_GreaterSQL($key, $val);
        $check = false;
      } else if (strstr($key, Sabel_DB_Driver_Interface::IN)) {
        $key = str_replace(Sabel_DB_Driver_Interface::IN, '', $key);
        $this->makeWhereInSQL($key, $val);
        $check = false;
      } else if (strstr($key, Sabel_DB_Driver_Interface::BET)) {
        $key = str_replace(Sabel_DB_Driver_Interface::BET, '', $key);
        $this->makeBetweenSQL($key, $val);
        $check = false;
      } else if (strstr($key, Sabel_DB_Driver_Interface::EITHER)) {
        $key = str_replace(Sabel_DB_Driver_Interface::EITHER, '', $key);
        $this->makeEitherSQL($key, $val);
        $check = false;
      } else if (strstr($key, Sabel_DB_Driver_Interface::LIKE)) {
        $key = str_replace(Sabel_DB_Driver_Interface::LIKE, '', $key);
        $this->makeLikeSQL($key, $val);
        $check = false;
      } else if (strtolower($val) === 'null') {
        $this->makeIsNullSQL($key);
        $check = false;
      } else if (strtolower($val) === 'not null') {
        $this->makeIsNotNullSQL($key);
        $check = false;
      } else {
        $this->makeNormalConditionSQL($key, $val);
      }
    }
    return $check;
  }

  public function makeConstraintQuery($constraints)
  {
    $this->makeConstraintSQL($constraints);
  }
}
