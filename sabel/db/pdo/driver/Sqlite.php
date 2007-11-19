<?php

/**
 * Sabel_DB_Pdo_Driver_Sqlite
 *
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_DB_Pdo_Driver_Sqlite extends Sabel_DB_Pdo_Driver
{
  public function getDriverId()
  {
    return "pdo-sqlite";
  }

  public function connect(array $params)
  {
    try {
      return new PDO("sqlite:" . $params["database"]);
    } catch (PDOException $e) {
      return $e->getMessage();
    }
  }

  public function getLastInsertId()
  {
    return $this->connection->lastInsertId();
  }
}
