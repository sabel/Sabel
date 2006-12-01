<?php

/**
 * Sabel_DB_Statement_Limitation
 *
 * @category   DB
 * @package    org.sabel.db
 * @subpackage statement
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_DB_Statement_Limitation
{
  private
    $sql    = array(),
    $limit  = null,
    $offset = null;

  public function __construct($sql, $limit, $offset)
  {
    $this->sql    = $sql;
    $this->limit  = $limit;
    $this->offset = $offset;
  }

  public function standardLimitation()
  {
    $sql =& $this->sql;
    if (isset($this->limit))  $sql[] = ' LIMIT '  . $this->limit;
    if (isset($this->offset)) $sql[] = ' OFFSET ' . $this->offset;
    return $sql;
  }

  public function firebirdLimitation()
  {
    $tmp = substr(join('', $this->sql), 6);

    if (isset($this->limit)) {
      $query  = "FIRST {$this->limit} ";
      $query .= (isset($this->offset)) ? "SKIP {$this->offset}" : 'SKIP 0';
      return array('SELECT ' . $query . $tmp);
    }

    if (isset($this->offset)) $this->sql = array('SELECT SKIP ' . $this->offset . $tmp);
    return $this->sql;
  }

  public function mssqlLimitation($column, $order)
  {
    $tmp = substr(join('', $this->sql), 6);

    if (isset($this->limit)) {
      $query = "TOP {$this->limit} ";
      if (isset($this->offset)) {
        list($subSelect, $orderStr) = $this->mssqlOffset($tmp, $column, $order);
        return array('SELECT ' . $query . $tmp . $subSelect . $orderStr);
      } else {
        return array('SELECT ' . $query . $tmp);
      }
    }

    if (isset($this->offset)) {
      list($subSelect, $orderStr) = $this->mssqlOffset($tmp, $column, $order);
      $this->sql = array('SELECT' . "{$tmp} " . $subSelect . $orderStr);
    }
    return $this->sql;
  }

  private function mssqlOffset(&$tmp, $column, $order)
  {
    if (isset($order)) {
      list($colName) = explode(' ', $order);
      $orderColumn = $colName;
      $orderStr = strstr($tmp, 'ORDER BY');
      $tmp = str_replace($orderStr, '', $tmp);
    } else {
      $orderColumn = $column;
      $orderStr = 'ORDER BY ' . $orderColumn;
    }
    $condition = strstr($tmp, 'WHERE');
    if ($condition) $tmp = str_replace($condition, '', $tmp);

    $sp = explode(' ', strstr($tmp, 'FROM'));
    $subSelect  = "WHERE $orderColumn NOT IN ";
    $subSelect .= "(SELECT TOP {$this->offset} $orderColumn FROM {$sp[1]} ";
    if ($condition) $subSelect .= "$condition ";

    $subSelect = $subSelect . $orderStr . ') ';
    if ($condition) $subSelect = "$subSelect AND " . str_replace('WHERE ', '', $condition);

    return array($subSelect, $orderStr);
  }
}
