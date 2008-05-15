<?php

/**
 * Sabel_Http_Response
 *
 * @category   Mail
 * @package    org.sabel.request
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Http_Response extends Sabel_Object
{
  /**
   * @var string
   */
  protected $responseText = "";
  
  /**
   * @var array
   */
  protected $headers = array();
  
  /**
   * @var int
   */
  protected $statusCode = 200;
  
  /**
   * @var string
   */
  protected $statusReason = "OK";
  
  /**
   * @var string
   */
  protected $contents = "";
  
  public function __construct($responseText)
  {
    $this->responseText = $responseText;
    
    // @todo chunked
    
    preg_match("/(\r\n|\n|\r)/", $responseText, $matches);
    
    if (!isset($matches[0])) {
      $this->headers  = array();
      $this->contents = "";
      return;
    }
    
    $eol = $matches[0];
    
    $headers = array();
    $_tmp = explode($eol, $responseText);
    
    foreach ($_tmp as $i => $line) {
      unset($_tmp[$i]);
      if ($line === "") break;
      if (strpos($line, "HTTP") === 0) {
        $exp = explode(" ", $line, 3);
        $this->statusCode = (int)$exp[1];
        $this->statusReason = $exp[2];
      } else {
        $exp = explode(":", $line, 2);
        $headers[$exp[0]] = ltrim($exp[1]);
      }
    }
    
    $this->headers  = $headers;
    $this->contents = implode($eol, $_tmp);
    
    if (isset($headers["Content-Encoding"]) && $headers["Content-Encoding"] === "gzip") {
      $this->contents = gzinflate(substr($this->contents, 10));
    }
  }
  
  public function getStatusCode()
  {
    return $this->statusCode;
  }
  
  public function getStatusReason()
  {
    return $this->statusReason;
  }
  
  public function getHeaders()
  {
    return $this->headers;
  }
  
  public function getHeader($name)
  {
    if (isset($this->headers[$name])) {
      return $this->headers[$name];
    } else {
      return "";
    }
  }
  
  public function getContents()
  {
    return $this->contents;
  }
}
