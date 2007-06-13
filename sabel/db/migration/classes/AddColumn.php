<?php

/**
 * Sabel_DB_Migration_Classes_AddColumn
 *
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_DB_Migration_Classes_AddColumn
{
  private $addColumns = array();

  public function column($name)
  {
    $column = new Sabel_DB_Migration_Classes_Column($name);
    return $this->addColumns[$name] = $column;
  }

  public function getAddColumns()
  {
    $columns = array();
    foreach ($this->addColumns as $column) {
      $columns[] = $column->getColumn();
    }

    return arrange($columns);
  }
}
