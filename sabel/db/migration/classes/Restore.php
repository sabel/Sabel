<?php

/**
 * Sabel_DB_Migration_Classes_Restore
 *
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_DB_Migration_Classes_Restore
{
  public static function forCreate($fp, $schema)
  {
    $columns = $schema->getColumns();
    self::forColumns($fp, $columns, '$create');

    $pkey = $schema->getPrimarykey();

    if (is_array($pkey)) {
      $pkeys = array();
      foreach ($pkey as $key) $pkeys[] = '"' . $key .'"';
      fwrite($fp, '$create->primary(array(' . implode(", ", $pkeys) . '));');
      fwrite($fp, "\n");
    }

    $uniques = $schema->getUniques();

    if ($uniques) {
      foreach ($uniques as $unique) {
        if (count($unique) === 1) {
          fwrite($fp, '$create->unique("' . $unique[0] . '");');
        } else {
          $us = array();
          foreach ($unique as $u) $us[] = '"' . $u . '"';
          fwrite($fp, '$create->unique(array(' . implode(", ", $us) . '));');
        }

        fwrite($fp, "\n");
      }
    }

    $fkeys = $schema->getForeignKeys();

    if ($fkeys) {
      foreach ($fkeys as $colName => $param) {
        $line = '$create->fkey("' . $colName . '")->table("'
              . $param["referenced_table"] . '")->column("'
              . $param["referenced_column"] . '")->onDelete("'
              . $param["on_delete"] . '")->onUpdate("'
              . $param["on_update"] . '");';

        fwrite($fp, $line . "\n");
      }
    }
  }

  public static function forColumns($fp, $columns, $variable = '$add')
  {
    $lines = array();
    foreach ($columns as $column) {
      $line = array($variable);
      $line[] = '->column("' . $column->name . '")';
      $line[] = '->type(' . $column->type . ')';

      $bool = ($column->nullable) ? "true" : "false";
      $line[] = '->nullable(' . $bool . ')';

      $bool = ($column->primary) ? "true" : "false";
      $line[] = '->primary(' . $bool . ')';

      $bool = ($column->increment) ? "true" : "false";
      $line[] = '->increment(' . $bool . ')';

      if ($column->default === null) {
        $line[] = '->default(_NULL)';
      } else {
        if ($column->isNumeric()) {
          $line[] = '->default(' . $column->default . ')';
        } elseif ($column->isBool()) {
          $bool = ($column->default) ? "true" : "false";
          $line[] = '->default(' . $bool . ')';
        } else {
          $line[] = '->default("' . $column->default . '")';
        }
      }

      if ($column->isString()) {
        $line[] = '->length(' . $column->max. ')';
      }

      $line[]  = ";";
      $lines[] = implode("", $line);
    }

    $lines = implode("\n", $lines);

    fwrite($fp, "<?php\n\n");
    fwrite($fp, $lines);
    fwrite($fp, "\n\n");
  }
}
