<?php

/**
 * Sabel_DB_Join_Structure
 *
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_DB_Join_Structure
{
  private static $ins  = null;
  private $structure   = array();
  private $joinObjects = array();

  private function __construct() {}

  public static function getInstance()
  {
    if (self::$ins === null) {
      self::$ins = new self();
    }

    return self::$ins;
  }

  public function add($source, $name)
  {
    $this->structure[$source][] = $name;
  }

  public function addJoinObject($object)
  {
    $this->joinObjects[$object->getName()] = $object;
  }

  public function getStructure($unset = true)
  {
    if ($unset) {
      $structure = $this->structure;
      $this->structure = array();
      return $structure;
    } else {
      return $this->structure;
    }
  }

  public function getJoinObjects($unset = true)
  {
    if ($unset) {
      $joinObjects = $this->joinObjects;
      $this->joinObjects = array();
      return $joinObjects;
    } else {
      return $this->joinObjects;
    }
  }

  public function setAlias($source, $alias)
  {
    $structure =& $this->structure;

    if (isset($structure[$source])) {
      $structure[$alias] = $structure[$source];
      unset($structure[$source]);
    }
  }

  public function clear()
  {
    $this->structure   = array();
    $this->joinObjects = array();

    self::$ins = null;
  }
}
