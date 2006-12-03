<?php

/**
 * Sabel_DB_Mssql_Statement
 *
 * @category   DB
 * @package    org.sabel.db
 * @subpackage mssql
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_DB_Mssql_Statement extends Sabel_DB_General_Statement
{
  protected $defOrderCol = '';

  protected function escapeLikeSQL($val)
  {
    $val = str_replace(array('%', '_'), array('[%]', '[_]'), $val);
    return array($val, null);
  }

  public function setDefaultOrderColumn($defCol)
  {
    $this->defOrderCol = $defCol;
  }

  protected function makeLimitationSQL(&$sql, $limit, $offset, $order)
  {
    $column = $this->defOrderCol;
    $tmp    = substr(join('', $sql), 6);

    if (isset($limit)) {
      $query = "TOP $limit ";
      if (isset($offset)) {
        list($subSelect, $orderStr) = $this->mssqlOffset($tmp, $column, $order, $offset);
        return array('SELECT ' . $query . $tmp . $subSelect . $orderStr);
      } else {
        return array('SELECT ' . $query . $tmp);
      }
    }

    if (isset($offset)) {
      list($subSelect, $orderStr) = $this->mssqlOffset($tmp, $column, $order, $offset);
      $sql = array('SELECT' . $tmp . $subSelect . $orderStr);
    }
  }

  protected function mssqlOffset(&$tmp, $column, $order, $offset)
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
    $subSelect  = " WHERE $orderColumn NOT IN ";
    $subSelect .= "(SELECT TOP $offset $orderColumn FROM {$sp[1]} ";
    if ($condition) $subSelect .= "$condition ";

    $subSelect = $subSelect . $orderStr . ') ';
    if ($condition) $subSelect = "$subSelect AND " . str_replace('WHERE ', '', $condition);

    return array($subSelect, $orderStr);
  }
}
