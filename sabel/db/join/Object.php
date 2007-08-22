<?php

/**
 * Sabel_DB_Join_Object
 *
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_DB_Join_Object extends Sabel_DB_Join_Template
{
  public function getProjection()
  {
    $projection = array();
    $name = ($this->hasAlias()) ? strtolower($this->aliasName) : $this->tblName;

    foreach ($this->columns as $column) {
      $hash = Sabel_DB_Join_ColumnHash::toHash("pre_{$name}_{$column}");
      $projection[] = $name . '.' . $column . ' AS "' . $hash . '"';
    }

    return implode(", ", $projection);
  }

  public function getJoinQuery($joinType)
  {
    $name  = $this->tblName;
    $keys  = $this->joinKey;
    $query = array(" $joinType JOIN $name ");

    if ($this->hasAlias()) {
      $name = strtolower($this->aliasName);
      $query[] = $name . " ";
    }

    $lower   = strtolower($this->sourceName);
    $query[] = "ON {$lower}.{$keys["fkey"]} = {$name}.{$keys["id"]} ";

    return implode("", $query);
  }
}
