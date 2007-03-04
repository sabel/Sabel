<?php

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
  protected $bridgeModel = '';

  public function getChild($child, $table = null)
  {
    if ($table === null && $this->bridgeModel === '')
      throw new Exception('Error: specify a name of a bridge model.');

    $table = (is_object($table) || $table === null) ? $this->bridgeModel : $table;
    parent::getChild($table);

    if ($bridges = $this->$table) {
      $children = array();
      foreach ($bridges as $bridge) $children[] = $bridge->$child;
      return $this->$child = $children;
    } else {
      return false;
    }
  }
}
