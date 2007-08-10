<?php

/**
 * Sabel_DB_Abstract_Transaction
 *
 * @abstract
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
abstract class Sabel_DB_Abstract_Transaction
{
  private static $instances = array();

  protected $active = false;
  protected $transactions = array();

  private function __construct() {}
  abstract public function commit();
  abstract public function rollback();

  public static function registInstance($instance)
  {
    self::$instances[] = $instance;
  }

  public static function getInstances()
  {
    return self::$instances;
  }

  public function isActive($connectionName = null)
  {
    if ($connectionName === null) {
      return $this->active;
    } else {
      return (isset($this->transactions[$connectionName]));
    }
  }

  public function clear()
  {
    $this->active = false;
    $this->transactions = array();
  }
}
