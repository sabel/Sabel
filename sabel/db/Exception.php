<?php

/**
 * Sabel_DB_Exception
 *
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_DB_Exception extends Exception
{
  const DISPLAY_TRACE_COUNT = 10;

  private static $traces = array();

  public static function displayError($method, $error, $thrown, $extra = null)
  {
    $message = array();

    $message["THROWN"]        = $thrown;
    $message["ERROR_MESSAGE"] = $error;
    $message["METHOD"]        = $method . "()";

    if ($extra !== null) {
      $message  = $message + $extra;
    }

    $traces = debug_backtrace();
    array_shift($traces);
    self::$traces = $traces;

    throw new self(print_r($message, true));
  }

  public static function displayTrace()
  {
    $i = 0;

    echo '<b><pre>';
    foreach (self::$traces as $trace) {
      if (isset($trace["file"])) {
        echo '<br/>';
        echo 'FILE: ' . $trace["file"] . '<br/>';
        echo '<font color="blue">';
        echo 'CALL: ' . $trace["class"] . $trace["type"] . $trace["function"] . '()<br/>';
        echo '</font>';

        $line  = $trace["line"];
        $lines = file($trace["file"]);

        echo '<div style="border: 1px solid green; margin-top: 10px; padding: 10px;">';
        echo '<code>';

        for ($j = $line - 6; $j < $line + 5; $j++) {
          if (!isset($lines[$j])) continue;

          $lineNum = $num = $j + 1;
          $len = strlen($num);
          for ($k = $len; $k < 6; $k++) $lineNum .= ' ';

          if ($num === $line) {
            echo '<font color="red">' . $lineNum . $lines[$j] . '</font>';
          } else {
            echo $lineNum . $lines[$j];
          }
        }

        echo '</code>';
        echo '</div>';
        echo '<br/>';
        echo '<hr/>';

        $i++;
      }

      if ($i === self::DISPLAY_TRACE_COUNT) break;
    }
    echo "</pre></b>";
  }

  protected static function createArguments($arguments)
  {
    $type = "(" . ucfirst(getType($arguments)) . ")";

    if (is_object($arguments)) {
      $arguments = $type . get_class($arguments);
    } elseif (is_resource($arguments)) {
      $arguments = $type . get_resource_type($arguments);
    } elseif (is_bool($arguments)) {
      $bool = ($arguments) ? "true" : "false";
      $arguments = $type . $bool;
    } else {
      $arguments = $type . $arguments;
    }

    return $arguments;
  }
}

