<?php

/**
 * Sabel_DB_Migration_Query
 *
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_DB_Migration_Query
{
  private
    $upgradeQueries   = array(),
    $downgradeQueries = array();
    
  public function upgrade($query)
  {
    if (is_string($query)) {
      $this->upgradeQueries[] = $query;
    } else {
      Sabel_Command::error("argument must be a string.");
      exit;
    }
  }
  
  public function downgrade($query)
  {
    if (is_string($query)) {
      $this->downgradeQueries[] = $query;
    } else {
      Sabel_Command::error("argument must be a string.");
      exit;
    }
  }
  
  public function execute()
  {
    if (Sabel_DB_Migration_Manager::isUpgrade()) {
      $queries = $this->upgradeQueries;
    } else {
      $queries = $this->downgradeQueries;
    }
    
    $stmt = Sabel_DB_Migration_Manager::getStatement();
    foreach ($queries as $query) $stmt->setQuery($query)->execute();
  }
}
