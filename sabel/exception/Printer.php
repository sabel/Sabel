<?php

/**
 * Sabel_Exception_ClassNotFound
 *
 * @category   Exception
 * @package    org.sabel.exception
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Exception_Printer
{
  public static function printTrace(Exception $exception, $eol = PHP_EOL, $return = false)
  {
    $result = array();
    
    foreach ($exception->getTrace() as $line) {
      $trace = array();
      
      if (isset($line["file"])) {
        $trace[] = "FILE: {$line["file"]}({$line["line"]})";
      } else {
        $trace[] = "FILE: Unknown";
      }
      
      $args = array();
      if (isset($line["args"]) && !empty($line["args"])) {
        foreach ($line["args"] as $arg) {
          if (is_object($arg)) {
            $args[] = "(Object)" . get_class($arg);
          } elseif (is_bool($arg)) {
            $args[] = ($arg) ? "true" : "false";
          } elseif (is_string($arg)) {
            $args[] = '"' . $arg . '"';
          } elseif (is_int($arg) || is_float($arg)) {
            $args[] = $arg;
          } elseif (is_resource($arg)) {
            $args[] = "(Resource)" . get_resource_type($arg);
          } elseif ($arg === null) {
            $args[] = "null";
          } else {
            $args[] = "(" . ucfirst(gettype($arg)) . ")" . $arg;
          }
        }
      }
      
      $args = implode(", ", $args);
      
      if (isset($line["class"])) {
        $trace[] = "CALL: " . $line["class"]
                 . $line["type"] . $line["function"] . "({$args})";
      } else {
        $trace[] = "FUNCTION: " . $line["function"] . "({$args})";
      }
      
      $result[] = implode($eol, $trace);
    }
    
    return implode($eol . $eol, $result);
  }
}
