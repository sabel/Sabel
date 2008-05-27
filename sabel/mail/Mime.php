<?php

/**
 * Sabel_Mail_Mime
 *
 * @category  Mail
 * @package   org.sabel.mail
 * @author    Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright 2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license   http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Mail_Mime extends Sabel_Object
{
  protected $headers = array();
  protected $content = null;
  protected $body = "";
  protected $parts = array();
  
  protected $isMbstringLoaded = false;

  public function __construct()
  {
    $this->isMbstringLoaded = extension_loaded("mbstring");
  }
  
  /**
   * @param string $key
   *
   * @return string
   */
  public function getHeader($key)
  {
    $lowered = strtolower($key);
    if (isset($this->headers[$lowered])) {
      return $this->headers[$lowered];
    } else {
      return "";
    }
  }
  
  /**
   * @return Sabel_Mail_Mime_Content
   */
  public function getContent()
  {
    return $this->content;
  }
  
  /**
   * @return string
   */
  public function getBody()
  {
    return $this->body;
  }
  
  /**
   * @return boolean
   */
  public function hasPart()
  {
    return !empty($this->parts);
  }
  
  /**
   * @return array
   */
  public function getParts()
  {
    return $this->parts;
  }
  
  /**
   * @param string $source
   *
   * @return string
   */
  public function decode($source)
  {
    $mail = $this->toHeadersAndBoby($source);
    return $this->_decode($mail["header"], $mail["body"]);
  }
  
  protected function _decode($headerText, $body, $ctype = "text/plain")
  {
    $mime = new self();
    $content = new Sabel_Mail_Mime_Content();
    $content->setType($ctype);
    
    $mime->headers = array_change_key_case($this->createHeaders($headerText));
    
    $transferEncoding = "7bit";
    
    foreach ($mime->headers as $key => $value) {
      switch ($key) {
        case "content-type":
          $values = $this->parseHeaderValue($value);
          $content->setType($values["value"]);
          if (isset($values["boundary"])) $content->setBoundary($values["boundary"]);
          if (isset($values["charset"])) $content->setCharset(strtoupper($values["charset"]));
          break;
          
        case "content-disposition":
          $values = $this->parseHeaderValue($value);
          $content->setDisposition($values["value"]);
          
          $filename = null;
          if (isset($values["filename"])) {
            $filename = $values["filename"];
          } elseif (isset($values["filename*0*"]) || isset($values["filename*0"])) {
            $buffer = array();
            foreach ($values as $k => $v) {
              if (strpos($k, "filename*") !== false) {
                $buffer[] = $v;
              }
            }
            
            $filename = implode("", $buffer);
          }
          
          if ($filename !== null) {
            $content->setName($this->decodeFileName($filename));
          }
          
          break;
          
        case "content-transfer-encoding":
          $values = $this->parseHeaderValue($value);
          $transferEncoding = $values["value"];
          break;
      }
    }
    
    switch (strtolower($content->getType())) {
      case "multipart/mixed":
      case "multipart/alternative":
      case "multipart/related":
      case "multipart/parallel":
      case "multipart/report":
      case "multipart/signed":
      case "multipart/digest":
      case "multipart/appledouble":
        if (($boundary = $content->getBoundary()) === "") {
          $message = __METHOD__ . "() Boundary Not Found.";
          throw new Sabel_Mail_Exception($message);
        }
        
        $parts = $this->splitByBoundary($body, $boundary);
        $ctype = (strtolower($content->getType()) === "multipart/digest") ? "message/rfc822" : "text/plain";
        
        foreach ($parts as $messagePart) {
          $part = $this->toHeadersAndBoby($messagePart);
          $mime->parts[] = $this->_decode($part["header"], $part["body"], $ctype);
        }
        break;
        
      case "message/rfc822":
        $mime->parts[] = $this->decode($body);
        break;
        
      default:
        $mime->body = $this->decodeString($body, $transferEncoding, $content->getCharset());
        break;
    }
    
    $mime->content = $content;
    return $mime;
  }

  /**
   * @param string $source
   *
   * @return array
   */
  protected function toHeadersAndBoby($source)
  {
    if (preg_match("/^(.+?)(\r\n\r\n|\n\n|\r\r)(.+)/s", $source, $matches) === 1) {
      return array("header" => $matches[1], "body" => $matches[3]);
    } else {
      return array("header" => "", "body" => $source);
    }
  }

  /**
   * @param string $headerText
   *
   * @return array
   */
  protected function createHeaders($headerText)
  {
    $headers = array();
    if ($headerText === "") return $headers;
    
    preg_match("/(\r\n|\n|\r)/", $headerText, $matches);
    
    if (!isset($matches[0])) {
      $_tmp = array($headerText);
    } else {
      $eol = $matches[0];
      $headerText = preg_replace("/{$eol}(\t|\s)+/", " ", $headerText);
      $_tmp = explode($eol, $headerText);
    }
    
    foreach ($_tmp as $i => $line) {
      unset($_tmp[$i]);
      if ($line === "") break;
      
      @list ($key, $value) = explode(":", $line, 2);
      $value = $this->decodeHeader(ltrim($value));
      
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
    
    return $headers;
  }

  /**
   * @param string $str
   *
   * @return array
   */
  protected function parseHeaderValue($str)
  {
    $values = array();
    $values["params"] = array();
    
    if (($pos = strpos($str, ";")) === false) {
      $values["value"] = $str;
      return $values;
    }
    
    $regex = '/".+[^\\\\]"|\'.+[^\\\\]\'/U';
    $str = preg_replace_callback($regex, create_function('$matches', '
        return str_replace(";", "__%SC%__", $matches[0]);
    '), $str);
    
    $values["value"] = substr($str, 0, $pos);
    $str = ltrim(substr($str, $pos + 1));
    if ($str === "" || $str === ";") return $values;
    
    foreach (array_map("trim", explode(";", $str)) as $param) {
      if ($param === "") continue;
      @list ($key, $value) = explode("=", $param, 2);
      $key = strtolower($key);
      
      if ($value === null) {
        $values["params"][] = $key;
      } else {
        $quote = $value{0};
        if ($quote === '"' || $quote === "'") {
          if ($quote === $value{strlen($value)-1}) {
            $value = str_replace("\\{$quote}", $quote, substr($value, 1, -1));
          }
        }
        
        $values[$key] = str_replace("__%SC%__", ";", $value);
      }
    }
    
    return $values;
  }
  
  /**
   * @param string $body
   * @param string $boundary
   *
   * @return array
   */
  protected function splitByBoundary($body, $boundary)
  {
    preg_match("/(\r\n|\n|\r)/", $body, $matches);
    $parts = array_map("ltrim", explode("--" . $boundary, $body));
    array_shift($parts);
    array_pop($parts);
    
    return $parts;
  }

  /**
   * @param string $str
   *
   * @return string
   */
  protected function decodeHeader($str)
  {
    $regex = "/=\?([^?]+)\?(q|b)\?([^?]*)\?=/i";
    $count = preg_match_all($regex, $str, $matches);
    if ($count < 1) return $str;
    
    $str = str_replace("?= =?", "?==?", $str);
    
    for ($i = 0; $i < $count; $i++) {
      $encoding = (strtolower($matches[2][$i]) === "b") ? "base64" : "quoted-printable";
      $value = $this->decodeString($matches[3][$i], $encoding, $matches[1][$i]);
      $str = str_replace($matches[0][$i], $value, $str);
    }
    
    return $str;
  }
  
  /**
   * @param string $filename
   *
   * @return string
   */
  protected function decodeFileName($filename)
  {
    if (preg_match("/^([a-zA-Z0-9\-]+)'([a-z]{2-5})?'(%.+)$/", $filename, $matches) === 1) {  // RFC2231
      return $this->decodeString(urldecode($matches[3]), "", $matches[1]);
    } elseif (preg_match("/=\?([^?]+)\?(q|b)\?([^?]*)\?=/i", $filename, $matches) === 1) {
      $encoding = (strtolower($matches[2]) === "b") ? "base64" : "quoted-printable";
      return $this->decodeString($matches[3], $encoding, $matches[1]);
    } else {
      return $filename;
    }
  }
  
  /**
   * @param string $str
   * @param string $encoding
   * @param string $charset
   *
   * @return string
   */
  protected function decodeString($str, $encoding, $charset)
  {
    switch (strtolower($encoding)) {
      case "base64":
        $str = base64_decode($str);
        break;
        
      case "quoted-printable":
        $str = quoted_printable_decode($str);
        break;
    }
    
    if ($this->isMbstringLoaded && $charset) {
      return $this->mbConvertEncoding($str, $charset);
    } else {
      return $str;
    }
  }
  
  /**
   * @param string $str
   * @param string $fromEnc
   *
   * @return string
   */
  protected function mbConvertEncoding($str, $fromEnc)
  {
    static $internalEncoding = null;
    
    if ($internalEncoding === null) {
      $internalEncoding = strtoupper(mb_internal_encoding());
    }
    
    $fromEnc = strtoupper($fromEnc);
    if ($internalEncoding === $fromEnc) {
      return $str;
    } else {
      return mb_convert_encoding($str, $internalEncoding, $fromEnc);
    }
  }
}
