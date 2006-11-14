<?php

/**
 * Sabel_DB_Condition
 *
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
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
    } elseif (strpos($key, self::LIKE) === 0) {
      $key  = str_replace(self::LIKE, '', $key);
      $type = self::LIKE;
    } elseif (strpos($key, self::BET) === 0) {
      $key  = str_replace(self::BET, '', $key);
      $type = self::BET;
    } elseif (strpos($key, self::COMP) === 0) {
      $key  = str_replace(self::COMP, '', $key);
      $type = self::COMP;
    } elseif ($val === self::ISNULL) {
      $type = self::ISNULL;
    } elseif ($val === self::NOTNULL) {
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

    if (strpos($key, '.') !== false) {
      list($mdlName, $key) = explode('.', $key);
      $key = convert_to_tablename($mdlName) . '.' . $key;
    }

    return array($key, $type, $val);
  }
}
