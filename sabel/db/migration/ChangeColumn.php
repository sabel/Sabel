<?php

/**
 * Sabel_DB_Migration_ChangeColumn
 *
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_DB_Migration_ChangeColumn
{
  private
    $mcolumns = array(),
    $columns  = array();

  public function column($name)
  {
    $column = new Sabel_DB_Migration_Column($name, true);
    return $this->mcolumns[$name] = $column;
  }

  public function getColumns()
  {
    $columns = array();
    foreach ($this->mcolumns as $column) {
      $columns[] = $column->getColumn();
    }

    return $columns;
  }
}