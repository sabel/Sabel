<?php

/**
 * Sabel_DB_Migration_Tools_Parser
 *
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_DB_Migration_Tools_Parser
{
  const IS_EMPTY = "MIGRATE_EMPTY_VALUE";

  protected $co      = null;
  protected $fkeys   = array();
  protected $uniques = array();

  public function toColumns($migClass, $filePath)
  {
    $fp = fopen($filePath, "r");

    $cols  = array();
    $lines = array();
    $opts  = array();
    $inOpt = false;

    while (!feof($fp)) {
      $line = trim(fgets($fp, 256));
      if (substr($line, 0, 1) === "#") continue;

      if ($line === "options:") {
        $inOpt = true;
        continue;
      }

      if ($inOpt) {
        $opts[] = $line;
      } elseif ($line === "" && !empty($lines)) {
        $cols[] = $this->toColumn($lines);
        $lines = array();
      } elseif ($line !== "") {
        $lines[] = $line;
      }
    }

    if (!empty($lines)) $cols[] = $this->toColumn($lines);
    if (!empty($opts))  $migClass->setOptions($opts);

    if (!empty($this->fkeys)) {
      $migClass->setForeignKeys($this->fkeys);
    }

    if (!empty($this->uniques)) {
      $migClass->setUniques($this->uniques);
    }

    fclose($fp);
    return $cols;
  }

  public function getDropColumns($filePath)
  {
    $fp   = fopen($filePath, "r");
    $cols = array();

    while (!feof($fp)) {
      $line = trim(fgets($fp, 256));
      if ($line === "") continue;
      $cols[] = $line;
    }

    fclose($fp);
    return $cols;
  }

  public function getUpgradeQuery($filePath)
  {
    $up    = false;
    $query = array();

    $fp = fopen($filePath, "r");

    while (!feof($fp)) {
      $line = trim(fgets($fp));
      if (substr($line, 0, 1) === "#") continue;

      if ($line === "") {
        if ($up) {
          break;
        } else {
          continue;
        }
      }

      $up = true;
      $query[] = $line;
    }

    fclose($fp);

    if (count($query) > 1) {
      return implode(" ", $query);
    } else {
      return $query[0];
    }
  }

  public function getDowngradeQuery($filePath)
  {
    $up    = false;
    $down  = false;
    $query = array();

    $fp = fopen($filePath, "r");

    while (!feof($fp)) {
      $line = trim(fgets($fp));
      if (substr($line, 0, 1) === "#") continue;

      if ($line === "") {
        if ($up) $down = true;
        continue;
      }

      $up = true;
      if ($down) $query[] = $line;
    }

    fclose($fp);

    if (count($query) > 1) {
      return implode(" ", $query);
    } else {
      return $query[0];
    }
  }

  protected function toColumn($lines)
  {
    $this->co = $co = new Sabel_DB_Schema_Column();

    $co->name      = $this->getName($lines);
    $co->type      = $this->getType($lines);
    $co->nullable  = $this->getNullable($lines);
    $co->default   = $this->getDefault($lines);
    $co->primary   = $this->getPrimary($lines);
    $co->increment = $this->getIncrement($lines);

    $this->setForeignKey($lines, $co);
    $this->setUnique($lines, $co);

    return $co;
  }

  private function getName(&$lines)
  {
    foreach ($lines as $num => $line) {
      if (substr($line, 0, 1) === "#") {
        unset($lines[$num]);
        continue;
      } else {
        $name = trim(substr($line, 0, strpos($line, ":")));
        unset($lines[$num]);
        return $name;
      }
    }
  }

  private function getType(&$lines)
  {
    foreach ($lines as $num => $line) {
      if (substr($line, 0, 4) === "type") {
        $value = $this->getValue($lines, $num, $line);
        if (strpos($value, "STRING") !== false) {
          $this->setLength(str_replace("STRING", "", $value));
          return constant("Sabel_DB_Type::STRING");
        } else {
          return constant("Sabel_DB_Type::{$value}");
        }
      }
    }

    return self::IS_EMPTY;
  }

  private function setLength($value)
  {
    if ($value === "") {
      $this->co->max = 255;
    } else {
      $this->co->max = (int)substr($value, 1, -1);
    }
  }

  private function getNullable(&$lines)
  {
    if (empty($lines)) return self::IS_EMPTY;

    foreach ($lines as $num => $line) {
      if (substr($line, 0, 8) === "nullable") {
        $value = $this->getValue($lines, $num, $line);
        return $this->toBooleanValue($value);
      }
    }

    return self::IS_EMPTY;
  }

  private function getDefault(&$lines)
  {
    if (empty($lines)) return self::IS_EMPTY;

    foreach ($lines as $num => $line) {
      if (substr($line, 0, 7) === "default") {
        $d = $this->getValue($lines, $num, $line);
        if (is_numeric($d)) return $d;

        if (substr($d, 0, 1) === "'" && substr($d, -1, 1) === "'") {
          return substr($d, 1, -1);
        } elseif (substr($d, 0, 1) === '"' && substr($d, -1, 1) === '"') {
          return substr($d, 1, -1);
        } elseif ($d === "NULL") {
          return null;
        } elseif ($d === "null") {
          throw new Exception("invalid parameter 'null'. => 'NULL'");
        } else {
          return $this->toBooleanValue($d);
        }
      }
    }

    return self::IS_EMPTY;
  }

  private function getPrimary(&$lines)
  {
    if (empty($lines)) return false;

    foreach ($lines as $num => $line) {
      if (substr($line, 0, 7) === "primary") {
        $value = $this->getValue($lines, $num, $line);
        return $this->toBooleanValue($value);
      }
    }

    return false;
  }

  private function getIncrement(&$lines)
  {
    if (empty($lines)) return false;

    foreach ($lines as $num => $line) {
      if (substr($line, 0, 9) === "increment") {
        $value = $this->getValue($lines, $num, $line);
        return $this->toBooleanValue($value);
      }
    }

    return false;
  }

  private function setForeignKey(&$lines, $co)
  {
    if (empty($lines)) return;

    foreach ($lines as $num => $line) {
      if (substr($line, 0, 4) === "fkey") {
        $value = $this->getValue($lines, $num, $line);
        $this->fkeys[$co->name] = $value;
      }
    }
  }

  private function setUnique(&$lines, $co)
  {
    if (empty($lines)) return;

    foreach ($lines as $num => $line) {
      if (substr($line, 0, 6) === "unique") {
        $value = $this->getValue($lines, $num, $line);
        if ($this->toBooleanValue($value)) {
          $this->uniques[] = $co->name;
        }
      }
    }
  }

  private function getValue(&$lines, $num, $line)
  {
    list (, $value) = explode(":", $line);
    unset($lines[$num]);

    return preg_replace("/\s+#.*$/", "", trim($value));
  }

  private function toBooleanValue($value)
  {
    if ($value === "TRUE") {
      return true;
    } elseif ($value === "FALSE") {
      return false;
    } else {
      throw new Exception("invalid parameter for boolean value. use TRUE or FALSE.");
    }
  }
}
