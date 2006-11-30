<?php

/**
 * Sabel_DB_Bridge
 *
 * @abstract
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
abstract class Sabel_DB_Bridge extends Sabel_DB_Relation
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
    if ($this->$table) {
      foreach ($this->$table as $bridge) $children[] = $bridge->$child;
      $this->$child = $children;
    }
  }
}
