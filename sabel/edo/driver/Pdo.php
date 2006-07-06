<?php

uses('sabel.edo.driver.Interface');
uses('sabel.edo.SQLObject');

class Sabel_Edo_Driver_Pdo implements Sabel_Edo_Driver_Interface
{
  private $pdo, $stmt, $sqlObj, $myDb;

  private $param = array();
  private $data  = array();
  private $keys  = array();

  public function __construct($conn, $myDb)
  {
    $this->pdo    = $conn;
    $this->myDb   = $myDb;
    $this->sqlObj = new PdoSQL();
  }

  public function setBasicSQL($sql)
  {
    $this->sqlObj->setBasicSQL($sql);
  }

  public function setUpdateSQL($table, $data)
  {
    $this->data = $data;

    $sql = "UPDATE {$table} SET";
    $set = false;

    foreach ($data as $key => $val) {
      if (!$set) {
        $sql .= " {$key}=:{$key}";
      } else {
        $sql .= ",{$key}=:{$key}";
      }
      $set = true;
    }

    $this->sqlObj->setBasicSQL($sql);
  }

  public function setInsertSQL($table, $data)
  {
  
  }

  public function executeInsert($table, $data)
  {
    $this->data = $data;

    if (count($this->keys) == count($data)) {
      $check = true;
      $count = 0;
      foreach ($data as $key => $val) {
        if ($this->keys[$count] != $key) {
          $check = false;
          break;
        }
        $count++;
      }
      if ($check)
        return $this->execute();
    }

    $sql = "INSERT INTO {$table}(";
 
    $set = false;
    $this->keys = array();

    foreach ($data as $key => $val) {
      if (!$set) {
        $sql .= "{$key}";
      } else {
        $sql .= ",{$key}";
      }
      $this->keys[] = $key;
      $set = true;
    }

    $sql .= ") VALUES(";
    $set = false;

    foreach ($data as $key => $val) {
      if (!$set) {
        $sql .= ":{$key}";
      } else {
        $sql .= ",:{$key}";
      }
      $set = true;
    }

    $sql .= ');';

    $this->sqlObj->setBasicSQL($sql);
    return $this->execute();
  }

  public function makeQuery($conditions, $constraints = null)
  {
    foreach ($conditions as $key => $val) {

      if ($val[0] == '>' || $val[0] == '<') {
        $this->sqlObj->makeLess_GreaterSQL($key, $val);
      } elseif (strstr($key, Sabel_Edo_Driver_Interface::IN)) {
        $key = str_replace(Sabel_Edo_Driver_Interface::IN, '', $key);
        $this->sqlObj->makeWhereInSQL($key, $val);
      } elseif (strstr($key, Sabel_Edo_Driver_Interface::BET)) {
        $key = str_replace(Sabel_Edo_Driver_Interface::BET, '', $key);
        $this->sqlObj->makeBetweenSQL($key, $val);
      } elseif (strstr($key, Sabel_Edo_Driver_Interface::EITHER)) {
        $key = str_replace(Sabel_Edo_Driver_Interface::EITHER, '', $key);
        $this->sqlObj->makeEitherSQL($key, $val);
      } elseif (strstr($key, Sabel_Edo_Driver_Interface::LIKE)) {
        $key = str_replace(Sabel_Edo_Driver_Interface::LIKE, '', $key);
        $this->sqlObj->makeLikeSQL($key, $val);
      } elseif (strtolower($val) == 'null') {
        $this->sqlObj->makeIsNullSQL($key);
      } elseif (strtolower($val) == 'not null') {
        $this->sqlObj->makeIsNotNullSQL($key);
      } else {
        $this->sqlObj->makeNormalConditionSQL($key, $val);
      }
    }

    if (!empty($constraints))
      $this->sqlObj->makeConstraintsSQL($constraints);
  }

  public function execute($sql = null)
  {
    if (is_null($sql)) {
      $sql = $this->sqlObj->getSQL();
      if (is_null($sql)) {
        throw new Exception('Error: None SQL-Query!! execute EDO::makeQuery() beforehand');
      }
    }

    $this->stmt = $this->pdo->prepare($sql);
    $this->makeBindParam();

    if (empty($this->param)) {
      return $this->stmt->execute();
    } else {
      $result = $this->stmt->execute($this->param);
      $this->param = array();
      return $result;
    }
  }

  public function fetch($style = null)
  {
    if ($style == Sabel_Edo_Driver_Interface::FETCH_ASSOC) {
      return $this->stmt->fetch(PDO::FETCH_ASSOC, $cursor, $offset);
    } else {
      return $this->stmt->fetch(PDO::FETCH_BOTH, $cursor, $offset);
    }
  }

  public function fetchAll($style = null)
  {
    if ($style == Sabel_Edo_Driver_Interface::FETCH_ASSOC) {
      return $this->stmt->fetchAll(PDO::FETCH_ASSOC);
    } else {
      return $this->stmt->fetchAll(PDO::FETCH_BOTH);
    }
  }

  private function makeBindParam()
  {
    $this->param = $this->sqlObj->getParam();

    if (!empty($this->param)) {
      if (!empty($this->data)) {
        $this->param = array_merge($this->param, $this->data);
        $this->data  = array();
      }
    } else {
      if (!empty($this->data)) {
        $this->param = $this->data;
        $this->data  = array();
      }
    }

    if (!empty($this->param)) {
      foreach ($this->param as $key => $val) {
        if (is_null($val)) continue;
        
        $this->param[":{$key}"] = $val;
        unset($this->param["{$key}"]);
      }
    }

    $this->sqlObj->unsetProparties();
  }
}

?>
