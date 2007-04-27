<?php

/**
 * Sabel_DB_Migration_Parser
 *
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_DB_Migration_Parser
{
  protected $co = null;

  public function toColumn($lines)
  {
    $this->co = $co = new Sabel_DB_Schema_Column();

    $co->name      = $this->getName($lines);
    $co->type      = $this->getType($lines);
    $co->nullable  = $this->getNullable($lines);
    $co->default   = $this->getDefault($lines);
    $co->primary   = $this->getPrimary($lines);
    $co->increment = $this->getIncrement($lines);

    return $co;
  }

  protected function getName(&$lines)
  {
    $line = array_shift($lines);
    return trim(str_replace(":", "", $line));
  }

  protected function getType(&$lines)
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

    return "EMPTY";
  }

  protected function setLength($value)
  {
    if ($value === "") {
      $this->co->max = 255;
    } else {
      $this->co->max = (int)substr($value, 1, -1);
    }
  }

  protected function getNullable(&$lines)
  {
    if (empty($lines)) return "EMPTY";

    foreach ($lines as $num => $line) {
      if (substr($line, 0, 8) === "nullable") {
        $value = $this->getValue($lines, $num, $line);
        return $this->toBooleanValue($value);
      }
    }

    return "EMPTY";
  }

  protected function getDefault(&$lines)
  {
    if (empty($lines)) return "EMPTY";

    foreach ($lines as $num => $line) {
      if (substr($line, 0, 7) === "default") {
        return $this->getValue($lines, $num, $line);
      }
    }

    return "EMPTY";
  }

  protected function getPrimary(&$lines)
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

  protected function getIncrement(&$lines)
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

  protected function getValue(&$lines, $num, $line)
  {
    list (, $value) = explode(":", $line);
    unset($lines[$num]);

    return trim($value);
  }

  protected function toBooleanValue($value)
  {
    return (in_array($value, array("true", "TRUE", 1)));
  }
}
