<?php

/**
 * Sabel_DB_Migration_Classes_Query
 *
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_DB_Migration_Classes_Query
{
  private
    $upgradeQueries   = array(),
    $downgradeQueries = array();

  public function upgrade($query)
  {
    if (is_string($query)) {
      $this->upgradeQueries[] = $query;
    } else {
      Sabel_Sakle_Task::error("query should be a string.");
      exit;
    }
  }

  public function downgrade($query)
  {
    if (is_string($query)) {
      $this->downgradeQueries[] = $query;
    } else {
      Sabel_Sakle_Task::error("query should be a string.");
      exit;
    }
  }

  public function execute()
  {
    $type = Sabel_DB_Migration_Manager::getApplyMode();

    $queries = ($type === "upgrade") ? $this->upgradeQueries
                                     : $this->downgradeQueries;

    foreach ($queries as $query) executeQuery($query);
  }
}
