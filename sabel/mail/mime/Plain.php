<?php

/**
 * Sabel_Mail_Mime_Plain
 *
 * @category   Mail
 * @package    org.sabel.mail
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Mail_Mime_Plain extends Sabel_Mail_Mime_Abstract
{
  /**
   * @var string
   */
  protected $type = Sabel_Mail_Mime_Abstract::PLAIN;
  
  public function __construct($content)
  {
    $this->content = $content;
  }
  
  /**
   * @return string
   */
  public function toMailPart()
  {
    $part = array();
    $eol  = Sabel_Mail::getEol();
    
    $part[] = "Content-Disposition: " . $this->disposition;
    $part[] = "Content-Transfer-Encoding: " . $this->encoding;
    $part[] = "Content-Type: {$this->type}; charset=" . $this->charset . $eol;
    $part[] = $this->getEncodedContent() . $eol;
    
    return implode($eol, $part);
  }
  
  /**
   * @return string
   */
  public function getEncodedContent()
  {
    $content = $this->content;
    if (extension_loaded("mbstring")) {
      $content = mb_convert_encoding($content, $this->charset);
    }
    
    $eol = Sabel_Mail::getEol();
    switch (strtolower($this->encoding)) {
      case "base64":
        $content = rtrim(chunk_split(base64_encode($content), Sabel_Mail::LINELENGTH, $eol));
        break;
      case "quoted-printable":
        $quoted  = Sabel_Mail_QuotedPrintable::encode($content, Sabel_Mail::LINELENGTH, $eol);
        $content = str_replace(array("?", " "), array("=3F", "=20"), $quoted);
        break;
    }
    
    return $content;
  }
}
