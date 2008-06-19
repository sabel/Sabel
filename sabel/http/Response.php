<?php

/**
 * Sabel_Http_Response
 *
 * @category   Mail
 * @package    org.sabel.http
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
   * @var Sabel_Http_Uri
   */
  protected $uri = null;
  
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
  protected $content = "";
  
  public function __construct($responseText)
  {
    $this->responseText = $this->content = $responseText;
    
    // @todo chunked
    
    preg_match("/(\r\n|\n|\r)/", $responseText, $matches);
    if (!isset($matches[0])) return;
    
    $eol = $matches[0];
    if (preg_match("/^(.+?)({$eol}{$eol})(.*)/s", $responseText, $matches) === 1) {
      $header = $matches[1];
      $this->content = $matches[3];
    } else {
      return;
    }
    
    $headers = array();
    foreach (explode($eol, $responseText) as $i => $line) {
      if ($line === "") break;
      if (strpos($line, "HTTP") === 0) {
        $exp = explode(" ", $line, 3);
        $this->statusCode = (int)$exp[1];
        $this->statusReason = $exp[2];
      } else {
        list ($key, $value) = explode(":", $line, 2);
        $value = ltrim($value);
        if (isset($headers[$key])) {
          if (is_array($headers[$key])) {
            $headers[$key][] = $value;
          } else {
            $headers[$key] = array($headers[$key], $value);
          }
        } else {
          $headers[$key] = $value;
        }
      }
    }
    
    $this->headers = array_change_key_case($headers);
    
    if ($this->getHeader("Content-Encoding") === "gzip") {
      $this->content = gzinflate(substr($this->content, 10));
    }
  }
  
  public function __toString()
  {
    return $this->responseText;
  }
  
  public function getStatusCode()
  {
    return $this->statusCode;
  }
  
  public function getStatusReason()
  {
    return $this->statusReason;
  }
  
  public function setUri(Sabel_Http_Uri $uri)
  {
    $this->uri = $uri;
  }
  
  public function getUri()
  {
    return $this->uri;
  }
  
  public function getHeaders()
  {
    return $this->headers;
  }
  
  public function addHeader($name, $value)
  {
    $this->headers[strtolower($name)] = $value;
  }
  
  public function getHeader($name)
  {
    $lowered = strtolower($name);
    if (isset($this->headers[$lowered])) {
      return $this->headers[$lowered];
    } else {
      return "";
    }
  }
  
  public function setContent($content)
  {
    $this->content = $content;
  }
  
  public function getContent()
  {
    return $this->content;
  }
}
