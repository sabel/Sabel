<?php

/**
 * Sabel_DB_Mssql_Driver
 *
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2004-2008 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */

// @todo implementation

/*
class Sabel_DB_Mssql_Driver extends Sabel_DB_Abstract_Driver
{
  public function getDriverId()
  {
    return "mssql";
  }

  public function connect(array $params)
  {
    $host = $params["host"];
    $host = (isset($params["port"])) ? $host . "," . $params["port"] : $host;
    $conn = mssql_connect($host, $params["user"], $params["password"], true);

    if ($conn) {
      mssql_select_db($params["database"], $conn);
      return $conn;
    } else {
      return mssql_get_last_message();
    }
  }

  public function begin($connectionName = null)
  {
    if ($connectionName === null) {
      $connectionName = $this->connectionName;
    } else {
      $this->setConnectionName($connectionName);
    }

    if (!Sabel_DB_Transaction::isActive($connectionName)) {
      $conn = $this->getConnection();
      if (!mssql_query("BEGIN TRANSACTION", $conn)) {
        throw new Sabel_DB_Exception("mssql driver begin failed.");
      }

      Sabel_DB_Transaction::start($conn, $this);
    }
  }

  public function commit($connection)
  {
    if (!mssql_query("COMMIT TRANSACTION", $connection)) {
      throw new Sabel_DB_Exception("mssql driver commit failed.");
    }
  }

  public function rollback($connection)
  {
    if (!mssql_query("ROLLBACK TRANSACTION", $connection)) {
      throw new Sabel_DB_Exception("mssql driver rollback failed.");
    }
  }

  public function close($connection)
  {
    mssql_close($connection);
    unset($this->connection);
  }

  public function escape(array $values)
  {
    foreach ($values as &$val) {
      if (is_bool($val)) {
        $val = ($val) ? "'true'" : "'false'";
      } elseif (is_string($val)) {
        $val = "'" . mssql_escape_string($val) . "'";
      }
    }

    return $values;
  }

  public function execute($sql, $bindParams = null)
  {
    if ($bindParams !== null) {
      $sql = $this->bind($sql, $this->escape($bindParams));
    }

    $conn   = $this->getConnection();
    $result = mssql_query($sql, $conn);

    if (!$result) $this->executeError($sql);

    $rows = array();
    if (is_resource($result)) {
      while ($row = mssql_fetch_assoc($result)) $rows[] = $row;
      mssql_free_result($result);
    }

    return (empty($rows)) ? null : $rows;
  }

  public function getLastInsertId()
  {
    $rows = $this->execute("SELECT SCOPE_IDENTITY() AS id");
    return $rows[0]["id"];
  }

  public function createSelectSql(Sabel_DB_Abstract_Statement $stmt)
  {
    $c = $constraints;
    $skipOrder = false;

    if (isset($c["group"]))  $sql .= " GROUP BY " . $c["group"];
    if (isset($c["having"])) $sql .= " HAVING "   . $c["having"];

    if (isset($c["limit"]) && isset($c["offset"])) {
      $rn   = "row_number() over (order by {$c["order"]}) as rn, ";
      $sql  = "SELECT * FROM ( SELECT " . $rn . substr($sql, 6) . ") tmp";
      $sql .= " WHERE rn between " . ($c["offset"] + 1) . " AND " . ($c["offset"] + $c["limit"]);
      $skipOrder = true;
    } elseif (isset($c["limit"]) && !isset($c["offset"])) {
      $sql = "SELECT TOP " . $c["limit"] . substr($sql, 6);
    } elseif (isset($c["offset"])) {
      $rn   = "row_number() over (order by {$c["order"]}) as rn, ";
      $sql  = "SELECT * FROM ( SELECT " . $rn . substr($sql, 6) . ") tmp";
      $sql .= " WHERE rn > {$c["offset"]}";
      $skipOrder = true;
    }

    if (!$skipOrder) {
      if (isset($c["order"])) $sql .= " ORDER BY " . $c["order"];
    }

    return $sql;
  }

  protected function createConstraintSql($constraints)
  {
    echo '@todo mssql createConstraintSql()';
    exit;
    
    $sql = "";

    if (isset($constraints["group"]))  $sql .= " GROUP BY " . $constraints["group"];
    if (isset($constraints["having"])) $sql .= " HAVING "   . $constraints["having"];
    if (isset($constraints["order"]))  $sql .= " ORDER BY " . $constraints["order"];

    return $sql;
  }

  private function executeError($sql)
  {
    $error   = mssql_get_last_message();
    $message = "mssql driver execute failed: $error, SQL: $sql";
    throw new Sabel_DB_Exception($message);
  }
}

function mssql_escape_string($val)
{
  return str_replace("'", "''", $val);
}
*/
