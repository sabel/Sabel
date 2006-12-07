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
  
  public function hyperlink($params, $anchor = null, $id = null, $class = null)
  {
    $uriPrefix = 'http://' . $_SERVER['HTTP_HOST'];
    
    if (is_object($anchor)) $anchor = $anchor->__toString();
    
    $fmtUri = '<a id="%s" class="%s" href="%s/%s">%s</a>';
    $uri = Sabel_Context::getCurrentCandidate()->uri($params);
    return sprintf($fmtUri, $id, $class, $uriPrefix, $uri, $anchor);
  }
  
  public function aTag($param, $anchor)
  {
    return $this->hyperlink($this->convert($param), $anchor);
  }
  
  public function uri($params, $withDomain)
  {
    $entry     = $this->getEntry($params);
    $uriPrefix = ($withDomain) ? 'http://' . $_SERVER['HTTP_HOST'] . '/' : '';
    $uri = Sabel_Context::getCurrentCandidate()->uri($params);
    return $uriPrefix . $uri;
  }
  
  private function convert($param)
  {
    $buf = array();
    $params = explode(',', $param);
    $reserved = ";";
    foreach ($params as $part) {
      $line     = array_map('trim', explode(':', $part));
      $reserved = ($line[0] === 'e') ? 'entry' : $line[0];
      $buf[$reserved] = $line[1];
    }
    return $buf;
  }
}