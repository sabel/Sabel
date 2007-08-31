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

  protected $traces  = array();
  protected $message = "";

  public function __construct($message, $code = 0)
  {
    $this->message = $message;
    $this->createTrace();

    parent::__construct($message, $code);
  }

  public function printTrace()
  {
    $i = 0;

    $html = array('<b><pre>');

    foreach ($this->traces as $trace) {
      if (isset($trace["file"])) {
        $html[] = '<br/>';
        $html[] = 'FILE: ' . $trace["file"] . '<br/>';
        $html[] = '<font color="blue">';
        $html[] = 'CALL: ' . $trace["class"] . $trace["type"] . $trace["function"] . '()<br/>';
        $html[] = '</font>';

        $line  = $trace["line"];
        $lines = file($trace["file"]);

        $html[] = '<div style="border: 1px solid green; margin-top: 10px; padding: 10px;">';
        $html[] = '<code>';

        for ($j = $line - 6; $j < $line + 5; $j++) {
          if (!isset($lines[$j])) continue;

          $lineNum = $num = $j + 1;
          $len = strlen($num);
          for ($k = $len; $k < 6; $k++) $lineNum .= ' ';

          if ($num === $line) {
            $html[] = '<font color="red">' . $lineNum . $lines[$j] . '</font>';
          } else {
            $html[] = $lineNum . $lines[$j];
          }
        }

        $html[] = '</code>';
        $html[] = '</div>';
        $html[] = '<br/>';
        $html[] = '<hr/>';

        $i++;
      }

      if ($i === self::DISPLAY_TRACE_COUNT) break;
    }

    $html[] = "</pre></b>";
    return implode("\n", $html);
  }

  private function createTrace()
  {
    $traces = debug_backtrace();

    array_shift($traces);
    array_shift($traces);
    array_shift($traces);

    $this->traces = $traces;
  }
}
