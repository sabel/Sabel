<?php

/**
 * Sabel_DB_Driver_Native_Query
 *
 * @category   DB
 * @package    org.sabel.db
 * @subpackage driver
 * @subpackage native
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_DB_Driver_Native_Query extends Sabel_DB_Driver_Statement
{
  public function makeUpdateSQL($table, $data)
  {
    $sql = array();
    foreach ($data as $key => $val) array_push($sql, "{$key}='{$this->escape($val)}'");
    $this->setBasicSQL("UPDATE $table SET " . join(',', $sql));
  }

  public function makeInsertSQL($table, $data)
  {
    $columns = array();
    $values  = array();

    foreach ($data as $key => $val) {
      array_push($columns, $key);
      array_push($values, "'{$this->escape($val)}'");
    }

    $sql = array("INSERT INTO $table (");
    array_push($sql, join(',', $columns));
    array_push($sql, ') VALUES(');
    array_push($sql, join(',', $values));
    array_push($sql, ')');

    return join('', $sql);
  }

  public function makeConstraintQuery($const)
  {
    if (isset($const['group']))  array_push($this->sql, ' GROUP BY ' . $const['group']);
    if (isset($const['having'])) array_push($this->sql, ' HAVING '   . $const['having']);

    $order = (isset($const['order'])) ? $const['order'] : null;
    if ($order) array_push($this->sql, ' ORDER BY ' . $const['order']);

    $limit  = (isset($const['limit']))  ? $const['limit']  : null;
    $offset = (isset($const['offset'])) ? $const['offset'] : null;
    $column = (isset($const['defCol'])) ? $const['defCol'] : null;

    $paginate = new Sabel_DB_Driver_Native_Paginate($this->sql, $limit, $offset);

    switch ($this->dbName) {
      case 'firebird':
        $this->sql = $paginate->firebirdPaginate();
        break;
      case 'mssql':
        $this->sql = $paginate->mssqlPaginate($column, $order);
        break;
      default:
        $this->sql = $paginate->standardPaginate();
        break;
    }
  }

  public function makeNormalSQL($condition)
  {
    $val = $condition->value;
    $this->setWhereQuery($this->getKey($condition) . "='{$this->escape($val)}'");
  }

  public function makeBetweenSQL($condition)
  {
    $val = $condition->value;
    $this->setWhereQuery($this->getKey($condition) . " BETWEEN '{$val[0]}' AND '{$val[1]}'");
  }

  public function makeLikeSQL($val, $condition, $esc = null)
  {
    $query = $this->getKey($condition) . " LIKE '{$this->escape($val)}'";
    if (isset($esc)) $query .= " escape '{$esc}'";
    $this->setWhereQuery($query);
  }

  public function makeCompareSQL($condition)
  {
    $lg  = $condition->value[0];
    $val = $this->escape($condition->value[1]);
    $this->setWhereQuery($condition->key . " $lg $val");
  }

  public function unsetProperties()
  {
    $this->set = false;
  }
}
