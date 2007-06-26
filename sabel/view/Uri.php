<?php

/**
 * Sabel_View_Uri
 *
 * @category   View
 * @package    org.sabel.view
 * @author     Mori Reo <mori.reo@gmail.com>
 * @copyright  2002-2006 Mori Reo <mori.reo@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_View_Uri
{
  private static $instance = null;
  
  public function __construct()
  {
  }
  
  public function hyperlink($params, $anchor = null)
  {
    if (is_object($anchor)) $anchor = $anchor->__toString();
    
    $fmtUri = "<a href='%s'>%s</a>";
    return sprintf($fmtUri, $this->uri($params), $anchor);
  }
  
  public function uri($param, $absolute = false, $secure = false)
  {
    $params = $this->convert($param);
    if (isset($_SERVER["SERVER_NAME"])) {
      $httphost = $_SERVER["SERVER_NAME"];
    } else {
      $httphost = "localhost";
    }
    
    $protocol = ($secure) ? 'https' : 'http';
    
    $uriPrefix = ($absolute) ? $protocol . '://' . $httphost : '';
    $context = Sabel_Context::getContext();
    $uri = $context->getCandidate()->uri($params);
    return $uriPrefix . "/" . $uri;
  }
  
  private function convert($param)
  {
    $buf = array();
    $params = explode(",", $param);
    $reserved = ";";
    foreach ($params as $part) {
      $line     = array_map("trim", explode(":", $part));
      $reserved = ($line[0] === 'n') ? "candidate" : $line[0];
      $buf[$reserved] = $line[1];
    }
    return $buf;
  }
}
