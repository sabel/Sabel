<?php

/**
 * Sabel_Mail_Body
 *
 * @category   Mail
 * @package    org.sabel.mail
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Mail_Body extends Sabel_Mail_Part
{
  const TEXT = "text/plain";
  const HTML = "text/html";
  
  /**
   * @var string
   */
  protected $text = "";
  
  /**
   * @var string
   */
  protected $type = self::TEXT;
  
  public function __construct($text, $type = self::TEXT)
  {
    $this->text = $text;
    $this->type = $type;
  }
  
  /**
   * @param string $type
   *
   * @return void
   */
  public function setType($type)
  {
    $this->type = $type;
  }
  
  /**
   * @return string
   */
  public function getType()
  {
    return $this->type;
  }
  
  /**
   * @param string $text
   *
   * @return void
   */
  public function setText($text)
  {
    $this->text = $text;
  }
  
  /**
   * @return string
   */
  public function getText()
  {
    return $this->text;
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
    $part[] = $this->getEncodedText() . $eol;
    
    return implode($eol, $part);
  }
  
  /**
   * @return string
   */
  public function getEncodedText()
  {
    $text = $this->text;
    if (extension_loaded("mbstring")) {
      $text = mb_convert_encoding($text, $this->charset);
    }
    
    $eol = Sabel_Mail::getEol();
    switch (strtolower($this->encoding)) {
      case "base64":
        $text = rtrim(chunk_split(base64_encode($text), Sabel_Mail::LINELENGTH, $eol));
        break;
      case "quoted-printable":
        $quoted = Sabel_Mail_QuotedPrintable::encode($text, Sabel_Mail::LINELENGTH, $eol);
        $text   = str_replace(array("?", " "), array("=3F", "=20"), $quoted);
        break;
    }
    
    return $text;
  }
}
