<?php

/**
 * Sabel_DB_Schema_Pgsql
 *
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_DB_Schema_Pgsql extends Sabel_DB_Schema_Common
{
  protected
    $tableList    = "SELECT table_name FROM information_schema.tables WHERE table_schema = '%s'",
    $tableColumns = "SELECT * FROM information_schema.columns WHERE table_schema = '%s' AND table_name = '%s'";

  public function isBoolean($type, $row)
  {
    return ($type === "boolean");
  }

  public function isFloat($type)
  {
    return ($type === "real" || $type === "double precision");
  }

  public function getFloatType($type)
  {
    return ($type === "real") ? "float" : "double";
  }

  public function setDefault($co, $row)
  {
    $default = $row["column_default"];

    if ($default === null || strpos($default, "nextval") !== false) {
      $co->default = null;
    } else {
      if (strpos($default, "'::") !== false) {
        preg_match("/'(.*)'/", $default, $matches);
        $default = $matches[1];
      }

      $this->setDefaultValue($co, $default);
    }
  }

  public function setIncrement($co, $row)
  {
    $sql = "SELECT * FROM pg_statio_user_sequences "
         . "WHERE relname = '{$row["table_name"]}_{$co->name}_seq'";

    $result = $this->execute($sql);
    $co->increment = !(empty($result));
  }

  public function setPrimaryKey($co, $row)
  {
    $sql = "SELECT * FROM information_schema.key_column_usage "
         . "WHERE table_schema = '{$this->schemaName}' "
         . "AND table_name = '{$row["table_name"]}' "
         . "AND column_name = '{$co->name}' AND constraint_name LIKE '%\_pkey'";

    $result = $this->execute($sql);
    $co->primary = !(empty($result));
  }

  public function setLength($co, $row)
  {
    $maxlen  = $row["character_maximum_length"];
    $co->max = (isset($maxlen)) ? $maxlen : 255;
  }
}
