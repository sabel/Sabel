<?php

/**
 * Sabel_DB_Migration_Column
 *
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_DB_Migration_Column
{
  private
    $column   = null,
    $isChange = false;

  public function __construct($name, $isChange = false)
  {
    $this->column = new Sabel_DB_Schema_Column();
    $this->column->name = $name;
    $this->isChange = $isChange;
  }

  public function getColumn()
  {
    return $this->column;
  }

  public function type($type)
  {
    $const = @constant($type);

    if ($const === null) {
      Sabel_Sakle_Task::error("datatype '{$type}' is not supported.");
      exit;
    } else {
      $this->column->type = $const;
    }

    return $this;
  }

  public function primary($bool)
  {
    $this->setBoolean($bool, "primary");
    return $this;
  }

  public function increment($bool)
  {
    $this->setBoolean($bool, "increment");
    return $this;
  }

  public function nullable($bool)
  {
    $this->setBoolean($bool, "nullable");
    return $this;
  }

  public function defaultValue($value)
  {
    if ($this->column->isBool() && !is_bool($value)) {
      Sabel_Sakle_Task::error("default value for BOOL column should be a boolean.");
      exit;
    } else {
      $this->column->default = $value;
      return $this;
    }
  }

  public function length($length)
  {
    if ($this->column->isString() || $this->isChange && $this->column->type === null) {
      $this->column->max = $length;
      return $this;
    } else {
      Sabel_Sakle_Task::error("length() for _STRING column.");
      exit;
    }
  }

  private function setBoolean($bool, $key)
  {
    if (is_bool($bool)) {
      $this->column->$key = $bool;
    } else {
      Sabel_Sakle_Task::error("argument for {$key}() should be a boolean.");
      exit;
    }
  }

  public function arrange()
  {
    $column = $this->column;

    if ($column->primary === true) {
      $column->nullable = false;
    } elseif ($column->nullable === null) {
      $column->nullable = true;
    }

    if ($column->primary === null) {
      $column->primary = false;
    }

    if ($column->increment === null) {
      $column->increment = false;
    }

    if ($column->type === Sabel_DB_Type::STRING &&
        $column->max === null) $column->max = 255;

    if ($column->type === Sabel_DB_Type::INT) {
      if ($column->max === null) $column->max = INT_MAX;
      if ($column->min === null) $column->min = INT_MIN;
    }

    if ($column->type === Sabel_DB_Type::SMALLINT) {
      if ($column->max === null) $column->max = SMALLINT_MAX;
      if ($column->min === null) $column->min = SMALLINT_MIN;
    }

    return $this;
  }
}
