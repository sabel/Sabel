<?php

/**
 * Sabel_DB_Migration_DropColumn
 *
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2004-2008 Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_DB_Migration_DropColumn
{
  private $columns = array();

  public function column($name)
  {
    $this->columns[] = $name;
  }

  public function getColumns()
  {
    return $this->columns;
  }
}
