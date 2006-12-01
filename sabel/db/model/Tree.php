<?php

/**
 * Sabel_DB_Model_Tree
 *
 * @abstract
 * @category   DB
 * @package    org.sabel.db
 * @subpackage model
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
abstract class Sabel_DB_Model_Tree extends Sabel_DB_Model_Relation
{
  protected $structure = 'tree';

  public function getRoot()
  {
    return $this->select("{$this->table}_id", Sabel_DB_Condition::ISNULL);
  }
}
