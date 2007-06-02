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
  const NOT     = "NOT";
  const NORMAL  = "NORMAL";
  const ISNULL  = "NULL";
  const NOTNULL = "NOTNULL";

  const BET  = "BET_";
  const IN   = "IN_";
  const LIKE = "LIKE_";
  const COMP = "COMP_";

  protected $values = array();

  public function __construct($key, $val, $option = null)
  {
    if ($option === self::NORMAL) {
      $this->values = array("key"   => $this->filterModelAlias($key),
                            "type"  => self::NORMAL,
                            "value" => $val);
    } else {
      list($key, $type) = $this->prepare($key, $val);

      $this->values = array("key"   => $key,
                            "type"  => $type,
                            "value" => $val,
                            "not"   => ($option === self::NOT));
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

  private function prepare($key, $val)
  {
    if (strpos($key, self::IN) === 0) {
      $key  = str_replace(self::IN, "", $key);
      $type = self::IN;
      if (!is_array($val)) {
        throw new Exception("parameter of 'IN_' should be an array.");
      }
    } elseif (strpos($key, self::LIKE) === 0) {
      $key  = str_replace(self::LIKE, "", $key);
      $type = self::LIKE;
    } elseif (strpos($key, self::BET) === 0) {
      $key  = str_replace(self::BET, "", $key);
      $type = self::BET;
    } elseif (strpos($key, self::COMP) === 0) {
      $key  = str_replace(self::COMP, "", $key);
      $type = self::COMP;
    } elseif ($val === self::ISNULL) {
      $type = self::ISNULL;
    } elseif ($val === self::NOTNULL) {
      $type = self::NOTNULL;
    } else {
      $type = self::NORMAL;
    }

    return array($this->filterModelAlias($key), $type);
  }

  private function filterModelAlias($key)
  {
    if (strpos($key, ".") !== false) {
      list($mdlName, $key) = explode(".", $key);
      $key = convert_to_tablename($mdlName) . "." . $key;
    }

    return $key;
  }
}
