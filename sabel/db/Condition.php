<?php

class Sabel_DB_Condition
{
  const NOT     = 'CONDITION_NOT';
  const NORMAL  = 'CONDITION_NORMAL';
  const ISNULL  = 'CONDITION_NULL';
  const NOTNULL = 'CONDITION_NOTNULL';

  const BET    = 'BET_';
  const IN     = 'IN_';
  const LIKE   = 'LIKE_';
  const COMP   = 'COMP_';

  protected $values = array();

  public function __construct($key, $val, $not = null)
  {
    list($key, $type, $value) = $this->getType($key, $val);

    $this->values['key']   = $key;
    $this->values['type']  = $type;
    $this->values['value'] = $value;
    $this->values['not']   = ($not === self::NOT);
  }

  public function __get($key)
  {
    return (isset($this->values[$key])) ? $this->values[$key] : null;
  }

  protected function getType($key, $val)
  {
    if (strpos($key, self::IN) === 0) {
      $key  = str_replace(self::IN, '', $key);
      $type = self::IN;
    } else if (strpos($key, self::LIKE) === 0) {
      $key  = str_replace(self::LIKE, '', $key);
      $type = self::LIKE;
    } else if (strpos($key, self::BET) === 0) {
      $key  = str_replace(self::BET, '', $key);
      $type = self::BET;
    } else if (strpos($key, self::COMP) === 0) {
      $key  = str_replace(self::COMP, '', $key);
      $type = self::COMP;
    } else if ($val === self::ISNULL) {
      $type = self::ISNULL;
    } else if ($val === self::NOTNULL) {
      $type = self::NOTNULL;
    } else {
      if (is_object($val)) {
        $errorMsg = 'Error: Sabel_DB_Condition::getType() invalid parameter. '
                  . 'the condition value is should not be an object.';

        throw new Exception($errorMsg);
      } else {
        $type = self::NORMAL;
      }
    }
    return array($key, $type, $val);
  }
}
