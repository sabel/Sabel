<?php

/**
 * Sabel_DB_Schema_Mysql_Mysql50
 *
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_DB_Schema_Mysql_Mysql50 extends Sabel_DB_Schema_Mysql_Base
{
  public function getForeignKeys($tblName, $driver)
  {
    $result = $driver->setSql("SHOW CREATE TABLE $tblName")->execute();
    $createSql = $result[0]["Create Table"];

    preg_match_all("/CONSTRAINT .+ FOREIGN KEY (.+)/", $createSql, $matches);
    if (empty($matches[1])) return null;

    $columns = array();
    foreach ($matches[1] as $match) {
      $tmp = explode(")", str_replace(" REFERENCES ", "", $match));
      $column = substr($tmp[0], 2, -1);

      $tmp2 = array_map("trim", explode("(", $tmp[1]));
      $columns[$column]["referenced_table"]  = substr($tmp2[0], 1, -1);
      $columns[$column]["referenced_column"] = substr($tmp2[1], 1, -1);

      $rule = trim($tmp[2]);
      $columns[$column]["on_delete"] = $this->getRule($rule, "ON DELETE");
      $columns[$column]["on_update"] = $this->getRule($rule, "ON UPDATE");
    }

    return $columns;
  }

  private function getRule($rule, $ruleName)
  {
    if (($pos = strpos($rule, $ruleName)) !== false) {
      $on = substr($rule, $pos + 10);
      if (($pos = strpos($on, " ON")) !== false) {
        $on = substr($on, 0, $pos);
      }

      return trim(str_replace(",", "", $on));
    } else {
      return "NO ACTION";
    }
  }
}
