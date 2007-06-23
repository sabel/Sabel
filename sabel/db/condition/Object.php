<?php

/**
 * Sabel_DB_Condition_Object
 *
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_DB_Condition_Object
{
  const ISNULL    = "CONDITION_NULL";
  const ISNOTNULL = "CONDITION_NOTNULL";
  const NORMAL    = "CONDITION_NORMAL";
  const IN        = "CONDITION_IN";
  const BETWEEN   = "CONDITION_BETWEEN";
  const LIKE      = "CONDITION_LIKE";
  const COMPARE   = "CONDITION_COMPARE";
  const NOT       = "CONDITION_NOT";

  protected $values = array();

  public function __construct($key, $val, $type = self::NORMAL, $not = false)
  {
    if (strpos($key, ".") !== false) {
      list($mdlName, $key) = explode(".", $key);
      $key = convert_to_tablename($mdlName) . "." . $key;
    }

    if ($val === self::ISNULL || $val === self::ISNOTNULL) {
      $this->values = array("key" => $key, "type"  => $val);
    } elseif($type === self::NOT) {
      $this->values = array("key" => $key, "value" => $val, "type" => self::NORMAL, "not" => true);
    } else {
      $not = ($not === self::NOT);
      $this->values = array("key" => $key, "value" => $val, "type" => $type, "not" => $not);
    }
  }

  public function __get($key)
  {
    return (isset($this->values[$key])) ? $this->values[$key] : null;
  }

  public function build($builder)
  {
    return $builder->build($this);
  }
}
