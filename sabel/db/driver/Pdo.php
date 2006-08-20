<?php

/**
 * db driver for PDO
 *
 * @author Ebine Yutaka <ebine.yutaka@gmail.com>
 * @package org.sabel.db
 */
class Sabel_DB_Driver_Pdo implements Sabel_DB_Driver_Interface
{
  private $pdo, $stmt, $queryObj, $myDb;

  private $param = array();
  private $data  = array();

  public function __construct($conn, $myDb)
  {
    $this->pdo      = $conn;
    $this->myDb     = $myDb;
    $this->queryObj = new Sabel_DB_Query_Bind();
  }

  public function begin()
  {
    //@todo
  }

  public function commit()
  {
    //@todo
  }

  public function setBasicSQL($sql)
  {
    $this->queryObj->setBasicSQL($sql);
  }

  public function setUpdateSQL($table, $data)
  {
    $sql = array();
    $this->data = $data;

    foreach (array_keys($data) as $key) array_push($sql, "{$key}=:{$key}");
    $this->queryObj->setBasicSQL("UPDATE {$table} SET " . join(',', $sql));
  }

  public function setAggregateSQL($table, $idColumn, $functions)
  {
    $sql = array("SELECT {$idColumn}");

    foreach ($functions as $key => $val)
      array_push($sql, ", {$key}({$val}) AS {$key}_{$val}");

    array_push($sql, " FROM {$table} GROUP BY {$idColumn}");
    $this->queryObj->setBasicSQL(join('', $sql));
  }

  public function executeInsert($table, $data, $defColumn)
  {
    if (is_null($data[$defColumn]) && $this->myDb === 'pgsql')
      $data[$defColumn] = $this->getNextNumber($table, $defColumn);

    $this->data = $data;

    $columns = array();
    foreach ($data as $key => $val) array_push($columns, $key);

    $values = array();
    foreach ($data as $key => $val) array_push($values, ':' . $key);

    $sql = array("INSERT INTO {$table}(");
    array_push($sql, join(',', $columns));
    array_push($sql, ") VALUES(");
    array_push($sql, join(',', $values));
    array_push($sql, ');');

    $this->stmtFlag = Sabel_DB_Driver_PdoStatement::exists(join('', $sql), $data);

    if (!$this->stmtFlag) $this->queryObj->setBasicSQL(join('', $sql));

    return $this->execute();
  }

  public function getLastInsertId()
  {
    switch ($this->myDb) {
      case 'pgsql':
        return $this->lastInsertId;
      case 'mysql':
        $this->execute('SELECT last_insert_id()');
        $row = $this->fetch(Sabel_DB_Driver_Interface::FETCH_ASSOC);
        return $row['last_insert_id()'];
      default:
        return 'todo else';
    }
  }

  private function getNextNumber($table, $defColumn = null)
  {
    $this->execute("SELECT nextval('{$table}_{$defColumn}_seq');");
    $row = $this->fetch();
    if (($this->lastInsertId = (int)$row[0]) === 0) {
      throw new Exception($table . '_id_seq is not found.');
    } else {
      return $this->lastInsertId;
    }
  }

  public function makeQuery($conditions, $constraints = null)
  {
    $exist = Sabel_DB_Driver_PdoStatement::exists($this->queryObj->getSQL(), $conditions, $constraints);

    $result = $this->queryObj->makeConditionQuery($conditions);
    if (!$result) $exist = false;

    if ($constraints && !$exsist)
      $this->queryObj->makeConstraintQuery($constraints);

    $this->stmtFlag = $exist;
  }

  public function execute($sql = null, $param = null)
  {
    if (isset($sql)) {
      $this->stmt = $this->pdo->prepare($sql);
    } else if ($this->stmtFlag) {
      $this->stmt = Sabel_DB_Driver_PdoStatement::get();
    } else if (is_null($this->queryObj->getSQL())) {
      throw new Exception('Error: query not exist. execute EDO::makeQuery() beforehand');
    } else {
      $sql = $this->queryObj->getSQL();
      if ($this->stmt = $this->pdo->prepare($sql)) {
        Sabel_DB_Driver_PdoStatement::add($this->stmt);
      } else {
        throw new Exception('PDOStatement is null. sql : ' . $sql);
      }
    }

    $this->makeBindParam();

    if ($this->stmt->execute($this->param)) {
      $this->param = array();
      return true;
    } else {
      // @todo
      var_dump($this->stmt->queryString);
      var_dump($this->param);
      throw new Exception('Error: PDOStatement::execute()');
    }
  }

  public function fetch($style = null)
  {
    if ($style === Sabel_DB_Driver_Interface::FETCH_ASSOC) {
      $result = $this->stmt->fetch(PDO::FETCH_ASSOC);
    } else {
      $result = $this->stmt->fetch(PDO::FETCH_BOTH);
    }
    $this->stmt->closeCursor();
    return $result;
  }

  public function fetchAll($style = null)
  {
    if ($style === Sabel_DB_Driver_Interface::FETCH_ASSOC) {
      return $this->stmt->fetchAll(PDO::FETCH_ASSOC);
    } else {
      return $this->stmt->fetchAll(PDO::FETCH_BOTH);
    }
  }

  private function makeBindParam()
  {
    $param = $this->queryObj->getParam();
    $data  = $this->data;

    if ($data)
      $param = (empty($param)) ? $data : array_merge($param, $data);

    if ($param) {
      foreach ($param as $key => $val) {
        if (is_null($val)) continue;

        $param[":{$key}"] = $val;
        unset($param[$key]);
      }
    }

    $this->param = $param;
    $this->data  = array();
    $this->queryObj->unsetProparties();
  }
}

class Sabel_DB_Driver_PdoStatement
{
  private static $stmt;
  private static $sql;
  private static $keys = array();
  private static $constraints = array();

  public static function exists($sql, $conditions, $constraints = null)
  {
    $result = true;
    if ($conditions) $keys = array_keys($conditions);

    if (self::$sql         != $sql  ||
        self::$keys        != $keys ||
        self::$constraints != $constraints) {

      self::$sql         = $sql;
      self::$keys        = $keys;
      self::$constraints = $constraints;
      $result = false;
    }

    return $result;
  }

  public static function add($stmt)
  {
    self::$stmt = $stmt;
  }

  public static function get()
  {
    return self::$stmt;
  }
}
