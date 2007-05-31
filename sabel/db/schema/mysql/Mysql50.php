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
class Sabel_DB_Schema_Mysql_Mysql50
{
  public function getForeignKeys($tblName, $driver)
  {
    $result = $driver->setSql("SHOW CREATE TABLE $tblName")->execute();
    $createSql = $result[0]["Create Table"];

    preg_match_all("/CONSTRAINT .+ FOREIGN KEY .+/", $createSql, $matches);
    if (empty($matches[0])) return null;

    $columns = array();
    $pattern = array("/CONSTRAINT .+ FOREIGN KEY /", "/ REFERENCES /");

    foreach ($matches[0] as $match) {
      $tmp = explode(")", preg_replace($pattern, "", $match));
      $column = substr($tmp[0], 2, -1);

      $tmp2 = array_map("trim", explode("(", $tmp[1]));
      $columns[$column]["referenced_table"]  = substr($tmp2[0], 1, -1);
      $columns[$column]["referenced_column"] = substr($tmp2[1], 1, -1);

      $rule = trim($tmp[2]);
      if (($pos = strpos($rule, "ON DELETE")) !== false) {
        $onDelete = substr($rule, $pos + 10);
        if (($pos = strpos($onDelete, " ON")) !== false) {
          $onDelete = substr($onDelete, 0, $pos);
        }

        $onDelete = trim(str_replace(",", "", $onDelete));
        $columns[$column]["on_delete"] = $onDelete;
      } else {
        $columns[$column]["on_delete"] = "NO ACTION";
      }

      $rule = trim($tmp[2]);

      if (($pos = strpos($rule, "ON UPDATE")) !== false) {
        $onUpdate = substr($rule, $pos + 10);
        if (($pos = strpos($onUpdate, " ON")) !== false) {
          $onUpdate = substr($onUpdate, 0, $pos);
        }

        $onUpdate = trim(str_replace(",", "", $onUpdate));
        $columns[$column]["on_update"] = $onUpdate;
      } else {
        $columns[$column]["on_update"] = "NO ACTION";
      }
    }

    return $columns;
  }
}
