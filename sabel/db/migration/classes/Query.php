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
  private $upgradeQueries   = "";
  private $downgradeQueries = "";

  public function upgrade($query)
  {
    $this->upgradeQueries[] = $query;
  }

  public function downgrade($query)
  {
    $this->downgradeQueries[] = $query;
  }

  public function getUpgradeQueries()
  {
    return $this->upgradeQueries;
  }

  public function getDowngradeQueries()
  {
    return $this->downgradeQueries;
  }
}
