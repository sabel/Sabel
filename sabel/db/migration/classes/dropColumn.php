<?php

/**
 * Sabel_DB_Migration_Classes_dropColumn
 *
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_DB_Migration_Classes_dropColumn
{
  private $dropColumns = array();

  public function column($name)
  {
    if (is_string($name)) {
      $this->dropColumns[] = $name;
    } else {
      Sabel_Sakle_Task::error("column name should be a string.");
      exit;
    }
  }

  public function getDropColumns()
  {
    return $this->dropColumns;
  }
}
