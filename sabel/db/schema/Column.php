<?php

/**
 * Sabel_DB_Schema_Column
 *
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_DB_Schema_Column
{
  public $name      = null;
  public $type      = null;
  public $nullable  = null;
  public $default   = null;
  public $primary   = null;
  public $increment = null;

  public function isInt($strict = false)
  {
    if ($strict) {
      return ($this->type === Sabel_DB_Type::INT);
    } else {
      return ($this->type === Sabel_DB_Type::INT    ||
              $this->type === Sabel_DB_Type::BIGINT ||
              $this->type === Sabel_DB_Type::SMALLINT);

    }
  }

  public function isBigint()
  {
    return ($this->type === Sabel_DB_Type::BIGINT);
  }

  public function isSmallint()
  {
    return ($this->type === Sabel_DB_Type::SMALLINT);
  }

  public function isFloat()
  {
    return ($this->type === Sabel_DB_Type::FLOAT);
  }

  public function isDouble()
  {
    return ($this->type === Sabel_DB_Type::DOUBLE);
  }

  public function isString()
  {
    return ($this->type === Sabel_DB_Type::STRING);
  }

  public function isText()
  {
    return ($this->type === Sabel_DB_Type::TEXT);
  }

  public function isDatetime()
  {
    return ($this->type === Sabel_DB_Type::DATETIME);
  }

  public function isBool()
  {
    return ($this->type === Sabel_DB_Type::BOOL);
  }

  public function isByte()
  {
    return ($this->type === Sabel_DB_Type::BYTE);
  }

  public function cast($value)
  {
    switch ($this->type) {
      case Sabel_DB_Type::INT:
        return (int)$value;

      case Sabel_DB_Type::STRING:
      case Sabel_DB_Type::TEXT:
      case Sabel_DB_Type::BIGINT:
        return (string)$value;

      case Sabel_DB_Type::FLOAT:
      case Sabel_DB_Type::DOUBLE:
        return (float)$value;

      case Sabel_DB_Type::BOOL:
        if (is_bool($value)) return $value;
        if (is_int($value))  return ($value === 1);

        return in_array($value, array("1", "t", "true"));

      case Sabel_DB_Type::DATETIME:
        return (is_object($value)) ? $value : new Sabel_Date($value);

      default:
        return $value;
    }
  }
}
