<?php

/**
 * Sabel_DB_Sql_Constraint_Mssql
 *
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_DB_Sql_Constraint_Mssql implements Sabel_DB_Sql_Constraint_Interface
{
  protected $orderColumn = "";

  public function setDefaultOrderColumn($columnName)
  {
    $this->orderColumn = $columnName;
  }

  public function build($sql, $constraints)
  {

  }

  /*
  // @todo
  protected function escapeLikeSQL($val)
  {
    $val = preg_replace('/([%_])/', '[$1]', $val);
    return array($val, null);
  }

  protected function makeLimitationSQL(&$sql, $limit, $offset, $order)
  {
    $column = $this->defOrderCol;
    $tmp    = substr(join('', $sql), 6);

    $limitQuery  = (isset($limit)) ? "TOP $limit " : '';
    $offsetQuery = (isset($offset))
                 ? $this->mssqlOffset($tmp, $column, $order, $offset) : '';

    return array('SELECT ' . $limitQuery . $tmp . $offsetQuery);
  }

  protected function mssqlOffset(&$tmp, $column, $order, $offset)
  {
    if (isset($order)) {
      list($orderColumn) = explode(' ', $order);
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

    return $subSelect . $orderStr;
    //return array($subSelect, $orderStr);
  }
  */
}
