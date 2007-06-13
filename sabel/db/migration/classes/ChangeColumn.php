<?php

/**
 * Sabel_DB_Migration_Classes_ChangeColumn
 *
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_DB_Migration_Classes_ChangeColumn
{
  private $changeColumns = array();

  public function column($name)
  {
    $column = new Sabel_DB_Migration_Classes_Column($name, true);
    return $this->changeColumns[$name] = $column;
  }

  public function getChangeColumns()
  {
    $columns = array();
    foreach ($this->changeColumns as $column) {
      $columns[] = $column->getColumn();
    }

    return $columns;
  }
}
