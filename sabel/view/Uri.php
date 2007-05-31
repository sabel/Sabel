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
  
  public function hyperlink($params, $anchor = null, $uriParameters = null, $id = null, $class = null, $secure = false, $absolute = false)
  {
    if (is_object($anchor)) $anchor = $anchor->__toString();
    
    $fmtUri = "<a id='%s' class='%s' href='%s%s'>%s</a>";
    return sprintf($fmtUri, $id, $class, $this->uri($params, $absolute, $secure), $uriParameters, $anchor);
  }
  
  public function aTag($param, $anchor, $uriParameters = null, $id, $class, $secure)
  {
    return $this->hyperlink($param, $anchor, $uriParameters, $id, $class, $secure);
  }
  
  public function uri($param, $withDomain = false, $secure = false)
  {
    $params = $this->convert($param);
    if (isset($_SERVER["SERVER_NAME"])) {
      $httphost = $_SERVER["SERVER_NAME"];
    } else {
      $httphost = "localhost";
    }
    
    $protocol = ($secure) ? 'https' : 'http';
    
    $uriPrefix = ($withDomain) ? $protocol . '://' . $httphost : '';
    $uri = Sabel_Context::getCandidate()->uri($params);
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
