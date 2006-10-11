<?php

class Sabel_DB_Condition
{
  const NOT    = 'NOT';
  const NORMAL = 'NORMAL';

  const EITHER = 'OR_';
  const BET    = 'BET_';
  const IN     = 'IN_';
  const LIKE   = 'LIKE_';

  protected $data = array();

  public function __construct($key, $value, $not = null)
  {
    list($key, $type) = $this->getType($key);

    $this->key   = $key;
    $this->type  = $type;
    $this->value = $value;
    $this->not   = ($not === self::NOT) ? self::NOT : false;
  }

  public function __set($key, $val)
  {
    $this->data[$key] = $val;
  }

  public function __get($key)
  {
    return (isset($this->data[$key])) ? $this->data[$key] : null;
  }

  protected function getType($key)
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
      $key  = $key;
      $type = self::NORMAL;
    }
    return array($key, $type);
  }
}
