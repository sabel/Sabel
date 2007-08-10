<?php

/**
 * Sabel_DB_Oci_Schema
 *
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_DB_Oci_Schema extends Sabel_DB_Abstract_Schema
{
  protected
    $tableList    = "SELECT table_name FROM all_tables WHERE owner = '%s'",
    $tableColumns = "SELECT table_name, column_name, data_type, data_precision, nullable, data_default,
                     char_length FROM all_tab_columns WHERE owner = '%s' AND table_name = '%s'";

  private
    $sequenceList = "SELECT sequence_name FROM all_sequences WHERE sequence_owner = '%s'",
    $primaryList  = "SELECT acc.column_name FROM all_cons_columns acc
                     INNER JOIN all_constraints ac ON ac.constraint_name = acc.constraint_name
                     WHERE ac.owner = '%s' AND ac.constraint_type = 'P' AND acc.table_name = '%s'";

  private
    $sequences   = array(),
    $primaryKeys = array();

  public function getTableLists()
  {
    $tables = array();
    $rows   = $this->execute(sprintf($this->tableList, $this->schemaName));

    foreach ($rows as $row) {
      $tables[] = strtolower($row["table_name"]);
    }

    return $tables;
  }

  public function getForeignKey($tblName)
  {
    $tblName = strtoupper($tblName);

    $ij  = "INNER JOIN";
    $cn  = "constraint_name";
    $sql = "SELECT acc.column_name, ac2.table_name AS ref_table, acc2.column_name AS ref_column, ac.delete_rule "
         . "FROM all_constraints ac {$ij} all_cons_columns acc ON acc.{$cn} = ac.{$cn} "
         . "{$ij} all_constraints ac2 ON ac2.{$cn} = ac.r_constraint_name "
         . "{$ij} all_cons_columns acc2 ON acc2.{$cn} = ac2.{$cn} "
         . "WHERE ac.owner = '{$this->schemaName}' AND ac.constraint_type = 'R' AND ac.table_name = '{$tblName}'";

    $rows = $this->execute($sql);
    if (empty($rows)) return null;

    $columns = array();
    foreach ($rows as $row) {
      $column = strtolower($row["column_name"]);
      $columns[$column]["referenced_table"]  = strtolower($row["ref_table"]);
      $columns[$column]["referenced_column"] = strtolower($row["ref_column"]);
      $columns[$column]["on_delete"]         = $row["delete_rule"];
      $columns[$column]["on_update"]         = "NO ACTION";
    }

    return $columns;
  }

  public function getUniques($tblName)
  {
    $tblName = strtoupper($tblName);

    $sql = "SELECT acc.constraint_name, acc.column_name FROM all_cons_columns acc "
         . "INNER JOIN all_constraints ac ON ac.constraint_name = acc.constraint_name "
         . "where ac.owner = '{$this->schemaName}' "
         . "AND ac.constraint_type = 'U' AND acc.table_name = '{$tblName}'";

    $rows = $this->execute($sql);
    if (empty($rows)) return null;

    $uniques = array();
    foreach ($rows as $row) {
      $key = $row["constraint_name"];
      $uniques[$key][] = strtolower($row["column_name"]);
    }

    return array_values($uniques);
  }

  protected function createColumns($tblName)
  {
    $sql  = sprintf($this->tableColumns, $this->schemaName, strtoupper($tblName));
    $rows = $this->execute($sql);

    $this->createSequences();
    $this->createPrimaryKeys($tblName);

    $columns = array();
    foreach ($rows as $row) {
      $colName = strtolower($row["column_name"]);
      $columns[$colName] = $this->makeColumnValueObject($row);
    }

    return $columns;
  }

  protected function makeColumnValueObject($row)
  {
    $co = new Sabel_DB_Schema_Column();
    $co->name = strtolower($row["column_name"]);
    $co->nullable = ($row["nullable"] !== "N");

    $type = strtolower($row["data_type"]);

    if ($type === "number") {
      if ($row["data_default"] === "1" || $row["data_default"] === "0") {
        $co->type = Sabel_DB_Type::BOOL;
      } else {
        $type = $this->toInternalNumberType($row["data_precision"]);
      }
    }

    if (!$co->isBool()) {
      if ($type === "float") {
        $type = ((int)$row["data_precision"] === 24) ? "float" : "double";
      } elseif ($type === "varchar2") {
        $type = "varchar";
      } elseif ($type === "clob") {
        $type = "text";
      } elseif ($type === "date") {
        $type = "datetime";
      }

      Sabel_DB_Type_Setter::send($co, $type);
    }

    $this->setDefault($co, $row);

    $seq = $row["table_name"] . "_" . $row["column_name"] . "_seq";
    $co->increment = (in_array(strtoupper($seq), $this->sequences));
    $co->primary   = (in_array($co->name, $this->primaryKeys));

    if ($co->primary) $co->nullable = false;
    if ($co->isString()) $co->max = (int)$row["char_length"];

    return $co;
  }

  protected function setDefault($co, $row)
  {
    $default = $row["data_default"];

    if ($default === null) {
      $co->default = null;
    } else {
      $default = trim($default);
      if (strcasecmp($default, "null") === 0) {
        $co->default = null;
      } elseif ($co->isString()) {
        $co->default = substr($default, 1, -1);
      } else {
        $this->setDefaultValue($co, $default);
      }
    }
  }

  private function toInternalNumberType($precision)
  {
    switch ($precision) {
      case "5":
        return "smallint";

      case "19":
        return "bigint";

      default:
        return "integer";
    }
  }

  private function createSequences()
  {
    if (!empty($this->sequences)) return;

    $seqs =& $this->sequences;
    $rows = $this->execute(sprintf($this->sequenceList, $this->schemaName));

    foreach ($rows as $row) {
      $seqs[] = $row["sequence_name"];
    }
  }

  private function createPrimaryKeys($tblName)
  {
    if (!empty($this->primaryKeys)) return;

    $keys =& $this->primaryKeys;
    $sql  = sprintf($this->primaryList, $this->schemaName, strtoupper($tblName));
    $rows = $this->execute($sql);

    foreach ($rows as $row) {
      $keys[] = strtolower($row["column_name"]);
    }
  }
}
