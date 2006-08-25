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
    if (isset($constraints['group']))
      array_push($this->sql, " GROUP BY {$constraints['group']}");

    if (isset($constraints['order']))
      array_push($this->sql, " ORDER BY {$constraints['order']}");

    if (isset($constraints['limit']))
      array_push($this->sql, " LIMIT {$constraints['limit']}");

    if (isset($constraints['offset']))
      array_push($this->sql, " OFFSET {$constraints['offset']}");
  }

  protected function setWhereQuery($query)
  {
    if ($this->set) {
      array_push($this->sql, ' AND ' . $query);
    } else {
      array_push($this->sql, ' WHERE ' . $query);
      $this->set = true;
    }
  }

  protected function toArrayEitherCondition($key, $val)
  {
    $keys = array();
    for ($i = 0; $i < count($val); $i++) $keys[] = $key;
    $condition = array();
    $condition[] = $keys;
    $condition[] = $val;
    return $condition;
  }
}
