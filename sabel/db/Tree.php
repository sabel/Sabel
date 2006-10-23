<?php

/**
 * Sabel_DB_Tree
 *
 * @abstract
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
abstract class Sabel_DB_Tree extends Sabel_DB_Wrapper
{
  protected $structure = 'tree';

  public function getRoot()
  {
    return $this->select("{$this->table}_id", Sabel_DB_Condition::ISNULL);
  }
}
