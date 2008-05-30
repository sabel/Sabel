<?php

/**
 * Sabel_Mail_Mime_Abstract
 *
 * @category   Mail
 * @package    org.sabel.mail
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
abstract class Sabel_Mail_Mime_Abstract
{
  const PLAIN = "text/plain";
  const HTML  = "text/html";
  const GIF   = "image/gif";
  const PNG   = "image/png";
  const JPEG  = "image/jpeg";
  
  /**
   * @var string
   */
  protected $charset = "ISO-8859-1";
  
  /**
   * @var string
   */
  protected $content = "";
  
  /**
   * @var string
   */
  protected $encoding = "7bit";
  
  /**
   * @var string
   */
  protected $disposition = "inline";
  
  /**
   * @return const Sabel_Mail_Mime_Abstract
   */
  public function getType()
  {
    return $this->type;
  }
  
  /**
   * @param string $content
   *
   * @return void
   */
  public function setContent($content)
  {
    $this->content = $content;
  }
  
  /**
   * @return string
   */
  public function getContent()
  {
    return $this->content;
  }
  
  /**
   * @param string $charset
   *
   * @return void
   */
  public function setCharset($charset)
  {
    $this->charset = $charset;
  }
  
  /**
   * @return string
   */
  public function getCharset()
  {
    return $this->charset;
  }
  
  /**
   * @param string $encoding
   *
   * @return void
   */
  public function setEncoding($encoding)
  {
    $this->encoding = strtolower($encoding);
  }
  
  /**
   * @return string
   */
  public function getEncoding()
  {
    return $this->encoding;
  }
  
  /**
   * @param string $disposition
   *
   * @return void
   */
  public function setDisposition($disposition)
  {
    $this->disposition = strtolower($disposition);
  }
  
  /**
   * @return string
   */
  public function getDisposition()
  {
    return $this->disposition;
  }
  
  /**
   * @return boolean
   */
  public function isPlain()
  {
    return ($this->type === self::PLAIN);
  }
  
  /**
   * @return boolean
   */
  public function isHtml()
  {
    return ($this->type === self::HTML);
  }
  
  /**
   * @return boolean
   */
  public function isFile()
  {
    return (!$this->isPlain() && !$this->isHtml());
  }
  
  protected function encode($str, $encoding, $eol = "\r\n", $length = Sabel_Mail::LINELENGTH)
  {
    if ($encoding === "base64") {
      return rtrim(chunk_split(base64_encode($str), $length, $eol));
    } elseif ($encoding === "quoted-printable") {
      $quoted = Sabel_Mail_QuotedPrintable::encode($str, $length, $eol);
      return str_replace(array("?", " "), array("=3F", "=20"), $quoted);
    } else {
      $message = __METHOD__ . "() invalid encoding.";
      throw new Sabel_Mail_Exception($message);
    }
  }
}
