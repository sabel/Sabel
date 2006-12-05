<?php

Sabel::using('Sabel_DB_Model');

/**
 * Sabel_DB_Model_Bridge
 *
 * @abstract
 * @category   DB
 * @package    org.sabel.db
 * @subpackage model
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
abstract class Sabel_DB_Model_Bridge extends Sabel_DB_Model
{
  protected $structure   = 'bridge';
  protected $bridgeTable = '';

  public function getChild($child, $table = null)
  {
    $this->enableParent();

    if ($table === null && $this->bridgeTable === '')
      throw new Exception('need bridge table name.');

    $table = (is_object($table) || $table === null) ? $this->bridgeTable : $table;
    parent::getChild($table);

    $children = array();
    $bridges  = $this->$table;

    if ($bridges) {
      $bridges = $this->$table;
      foreach ($bridges as $bridge) $children[] = $bridge->$child;
      return $this->$child = $children;
    } else {
      return false;
    }
  }
}
