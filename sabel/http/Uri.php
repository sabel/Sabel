<?php

/**
 * Sabel_Http_Uri
 *
 * @category   Mail
 * @package    org.sabel.http
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Http_Uri extends Sabel_ValueObject
{
  public function __construct($uri)
  {
    $parsed     = parse_url($uri);
    $this->host = $parsed["host"];
    $this->path = $parsed["path"];
    
    if (isset($parsed["query"])) {
      $this->query = $parsed["query"];
    } else {
      $this->query = "";
    }
    
    $this->scheme = $scheme = $parsed["scheme"];
    
    if ($scheme === "http") {
      $this->port = (isset($parsed["port"])) ? $parsed["port"] : 80;
      $this->transport = "tcp";
    } elseif ($scheme === "https") {
      $this->transport = "ssl";
      $this->port = (isset($parsed["port"])) ? $parsed["port"] : 443;
    } elseif ($scheme === "ftp") {
      $this->transport = "ftp";
      $this->port = (isset($parsed["port"])) ? $parsed["port"] : 21;
    } else {
      // @todo
    }
  }
  
  public function setPath($path)
  {
    $parsed = parse_url("http://localhost" . $path);
    $this->path = $parsed["path"];
    
    if (isset($parsed["query"])) {
      $this->query = $parsed["query"];
    } else {
      $this->query = "";
    }
  }
}
