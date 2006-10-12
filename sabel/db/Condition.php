<?php

class Sabel_DB_Condition
{
  const NOT     = 'NOT';

  const NORMAL  = 'NORMAL';
  const ISNULL  = 'NULL';
  const NOTNULL = 'NOTNULL';
  const COMPARE = 'COMPARE';

  const EITHER  = 'OR_';
  const BET     = 'BET_';
  const IN      = 'IN_';
  const LIKE    = 'LIKE_';

  protected $values = array();

  public function __construct($key, $val, $not = null)
  {
    list($key, $type, $value) = $this->getType($key, $val);

    $this->values['key']   = $key;
    $this->values['type']  = $type;
    $this->values['value'] = $value;
    $this->values['not']   = ($not === self::NOT) ? self::NOT : false;
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
    } else if (strpos($key, self::EITHER) === 0) {
      $key  = str_replace(self::EITHER, '', $key);
      $type = self::EITHER;
    } else {
      if ($val[0] === '>' || $val[0] === '<') {
        $type = self::COMPARE;
      } else if (strtolower($val) === 'null') {
        $type = self::ISNULL;
      } else if (strtolower($val) === 'not null') {
        $type = self::NOTNULL;
      } else {
        $type = self::NORMAL;
      }
    }
    return array($key, $type, $val);
  }
}
