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

  public function isInt()
  {
    return ($this->type === Sabel_DB_Type::INT);
  }

  public function isSmallint()
  {
    return ($this->type === Sabel_DB_Type::SMALLINT);
  }

  public function isBigint()
  {
    return ($this->type === Sabel_DB_Type::BIGINT);
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
}
