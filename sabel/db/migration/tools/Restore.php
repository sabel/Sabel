<?php

/**
 * Sabel_DB_Migration_Tools_Restore
 *
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_DB_Migration_Tools_Restore
{
  public static function write($fp, $schema = null, $columns = null)
  {
    if ($columns === null) {
      $columns = $schema->getColumns();
    }

    if ($schema === null) {
      $fkeys = array();
    } else {
      $fkeys = $schema->getForeignKeys();
    }

    $pkeys = array();

    foreach ($columns as $column) {
      fwrite($fp, $column->name . ":\n");
      if ($column->isString()) {
        fwrite($fp, "  type: " . $column->type . "(" . $column->max . ")\n");
      } else {
        fwrite($fp, "  type: " . $column->type . "\n");
      }

      if ($column->nullable) {
        fwrite($fp, "  nullable: TRUE\n");
      } else {
        fwrite($fp, "  nullable: FALSE\n");
      }

      if ($schema === null) {
        if ($column->primary) fwrite($fp, "  primary: TRUE\n");
      } else {
        if ($column->primary) $pkeys[] = $column->name;
      }

      if ($column->increment) fwrite($fp, "  increment: TRUE\n");

      $d = $column->default;
      if ($d === null) {
        fwrite($fp, "  default: NULL");
      } elseif ($column->isBool()) {
        if ($d) {
          fwrite($fp, "  default: TRUE");
        } else {
          fwrite($fp, "  default: FALSE");
        }
      } elseif (is_int($d) || is_float($d)) {
        fwrite($fp, "  default: $d");
      } else {
        fwrite($fp, "  default: '{$d}'");
      }

      if (isset($fkeys[$column->name])) {
        $fkey = $fkeys[$column->name];
        $line = "fkey: {$fkey["referenced_table"]}({$fkey["referenced_column"]}) "
              . "ON DELETE {$fkey["on_delete"]} ON UPDATE {$fkey["on_update"]}";

        fwrite($fp, "\n  " . $line);
      }

      fwrite($fp, "\n\n");
    }

    if ($schema !== null) {
      $uniques = $schema->getUniques();
      self::writeConstraint($fp, $uniques, $pkeys);
    }
  }

  private function writeConstraint($fp, $uniques, $pkeys)
  {
    $write = (empty($pkeys));

    if ($pkeys) {
      fwrite($fp, "constraint:\n");
      fwrite($fp, "  primary: " . implode(", ", $pkeys) . "\n");
    }

    if (empty($uniques)) {
      fwrite($fp, "\n");
      return;
    }

    if ($write) fwrite($fp, "constraint:\n");

    foreach ($uniques as $unique) {
      $line = "  unique: " . implode(", ", $unique);
      fwrite($fp, $line . "\n");
    }

    fwrite($fp, "\n");
  }
}
