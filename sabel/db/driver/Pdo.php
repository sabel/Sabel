<?php

/**
 * Sabel_DB_Driver_Pdo
 *
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_DB_Driver_Pdo extends Sabel_DB_Driver_Base
{
  protected $db = "";
  protected $bindValues = array();

  public function __construct($db)
  {
    $this->db = $db;
  }

  public function setBindValues($bindValues, $add = true)
  {
    if ($add) {
      foreach ($bindValues as $key => $val) {
        $this->bindValues[$key] = $val;
      }
    } else {
      $this->bindValues = $bindValues;
    }
  }

  public function getBinds()
  {
    return $this->bindValues;
  }

  public function getAfterMethods()
  {
    return array("insert" => array("getIncrementId"));
  }

  public function getSqlClass($model, $classType = null)
  {
    return parent::getSqlClass($model, Sabel_DB_Sql_Loader::PDO);
  }

  public function getConditionBuilder($classType = null)
  {
    return parent::getConditionBuilder(Sabel_DB_Condition_Builder_Loader::PDO);
  }

  public function begin($connectionName)
  {
    $trans = $this->loadTransaction();

    if (!$trans->isActive($connectionName)) {
      $connection = Sabel_DB_Connection::get($connectionName);
      $connection->beginTransaction();
      $trans->start($connection, $this);
    }
  }

  public function commit($connection)
  {
    if (!$connection->commit()) {
      $error = $connection->errorInfo();
      throw new Exception("transaction commit failed. {$error[2]}");
    }
  }

  public function rollback($connection)
  {
    $connection->rollBack();
  }

  public function close($connection)
  {
    $connection = null;
  }

  public function escape($values)
  {
    return escapeString($this->db, $values);
  }

  public function getIncrementId($command = null)
  {
    switch ($this->db) {
      case 'pgsql':
        $id = Sabel_DB_Driver_Sequence::getId("pgsql", $command);
        break;

      case 'mysql':
        $id = Sabel_DB_Driver_Sequence::getId("mysql", $command);
        break;

      case 'sqlite':
        $id = (int)$this->connection->lastInsertId();
        break;
    }

    if ($command === null) {
      return $id;
    } else {
      $command->setIncrementId($id);
    }
  }

  public function execute($conn = null)
  {
    $sql = $this->sql;

    // @todo
    if (defined("QUERY_LOG") && ENVIRONMENT === DEVELOPMENT) {
      var_dump($sql);
    }

    if ($conn === null) $conn = $this->getPdoInstance();

    if (is_array($sql)) {
      $this->arrayExecute($conn, $sql);
    } else {
      $pdoStmt = $this->createPdoStatement($conn, $sql);
      $param   = $this->createBindParam();

      if ($pdoStmt->execute($param)) {
        $this->result = $pdoStmt->fetchAll(PDO::FETCH_ASSOC);
        $pdoStmt->closeCursor();
        return $this->result;
      } else {
        if (is_object($pdoStmt)) {
          $error = $pdoStmt->errorInfo();
          $sql   = $pdoStmt->queryString;
        } else {
          $error = $conn->errorInfo();
        }

        $error = (isset($error[2])) ? $error[2] : print_r($error, true);
        $param = (empty($param)) ? null : $param;

        $this->error("pdo driver execute failed: $error", $sql, $param);
      }
    }
  }

  protected function getPdoInstance()
  {
    if ($this->connection === null) {
      $conn = Sabel_DB_Connection::get($this->connectionName);
      $this->connection = $conn;
    } else {
      $conn = $this->connection;
    }

    return $conn;
  }

  protected function createPdoStatement($conn, $sql)
  {
    if (!($pdoStmt = $conn->prepare($sql))) {
      $this->data = array();
      $error = $conn->errorInfo();
      $this->error("PdoStatement is invalid. {$error[2]}", $sql);
    }

    return $pdoStmt;
  }

  protected function arrayExecute($conn, $sqls)
  {
    $bindValues = $this->bindValues;

    foreach ($sqls as $sql) {
      $pdoStmt = $this->createPdoStatement($conn, $sql);
      $param   = $this->createBindParam(array_shift($bindValues));
      $pdoStmt->execute($param);
    }
  }

  protected function createBindParam($bindValues = null)
  {
    $bindParam = array();
    $binds = ($bindValues === null) ? $this->bindValues : $bindValues;

    foreach ($binds as $key => $value) {
      $bindParam[":{$key}"] = $value;
    }

    $this->bindValues = array();
    return $bindParam;
  }
}
