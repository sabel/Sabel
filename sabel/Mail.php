<?php

/**
 * Sabel_Mail
 *
 * @category   Mail
 * @package    org.sabel.mail
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Mail extends Sabel_Object
{
  const LINELENGTH = 74;
  
  /**
   * @var Sabel_Mail_Sender_Interface
   */
  protected $sender = null;
  
  /**
   * @var Sabel_Mail_Body
   */
  protected $bodyText = null;
  
  /**
   * @var Sabel_Mail_Body
   */
  protected $bodyHtml = null;
  
  /**
   * @var string
   */
  protected $boundary = "";
  
  /**
   * @var array
   */
  protected $headers = array();
  
  /**
   * @var array
   */
  protected $attachments = array();
  
  /**
   * @var boolean
   */
  protected $isMbstringLoaded = false;
  
  /**
   * @var string
   */
  protected static $EOL = "\r\n";
  
  public function __construct($charset = "ISO-8859-1", $eol = "\r\n")
  {
    self::$EOL = $eol;
    
    $this->charset = $charset;
    $this->isMbstringLoaded = extension_loaded("mbstring");
  }
  
  public static function setEol($eol)
  {
    self::$EOL = $eol;
  }
  
  public static function getEol()
  {
    return self::$EOL;
  }
  
  public function setCharset($charset)
  {
    $this->charset = $charset;
  }
  
  public function getCharset()
  {
    return $this->charset;
  }
  
  public function setSender(Sabel_Mail_Sender_Interface $sender)
  {
    $this->sender = $sender;
  }
  
  public function setFrom($from, $name = "")
  {
    if ($name === "") {
      $this->headers["From"] = array("address" => $from, "name" => "");
    } else {
      $this->headers["From"] = array("address" => $from, "name" => $this->encodeHeader($name));
    }
    
    return $this;
  }
  
  public function setBoundary($boundary)
  {
    $this->boundary = $boundary;
    
    return $this;
  }
  
  public function getBoundary()
  {
    if ($this->boundary === "") {
      return $this->boundary = md5hash();
    } else {
      return $this->boundary;
    }
  }
  
  public function addTo($to, $name = "")
  {
    if ($name === "") {
      $to = array("address" => $to, "name" => "");
    } else {
      $to = array("address" => $to, "name" => $this->encodeHeader($name));
    }
    
    if (isset($this->headers["To"])) {
      $this->headers["To"][] = $to;
    } else {
      $this->headers["To"] = array($to);
    }
    
    return $this;
  }
  
  public function setTo($to)
  {
    if (isset($this->headers["To"])) {
      $this->headers["To"] = array();
    }
    
    if (is_string($to)) {
      $this->headers["To"] = array(array("address" => $to, "name" => ""));
    } elseif (is_array($to)) {
      foreach ($to as $recipient) {
        $this->headers["To"][] = array("address" => $recipient, "name" => "");
      }
    } else {
      $message = __METHOD__ . "() argument must be a string or an array.";
      throw new Sabel_Exception_InvalidArgument($message);
    }
  }
  
  public function addCc($to, $name = "")
  {
    if ($name === "") {
      $to = array("address" => $to, "name" => "");
    } else {
      $to = array("address" => $to, "name" => $this->encodeHeader($name));
    }
    
    if (isset($this->headers["Cc"])) {
      $this->headers["Cc"][] = $to;
    } else {
      $this->headers["Cc"] = array($to);
    }
    
    return $this;
  }
  
  public function addBcc($to)
  {
    if (isset($this->headers["Bcc"])) {
      $this->headers["Bcc"][] = $to;
    } else {
      $this->headers["Bcc"] = array($to);
    }
    
    return $this;
  }
  
  public function setSubject($subject)
  {
    $this->headers["Subject"] = $this->encodeHeader($subject);
    
    return $this;
  }
  
  public function setBodyText($text, $encoding = "7bit", $disposition = "inline")
  {
    $this->bodyText = new Sabel_Mail_Body($text, Sabel_Mail_Body::TEXT);
    $this->bodyText->setEncoding($encoding);
    $this->bodyText->setDisposition($disposition);
    
    return $this;
  }
  
  public function setBodyHtml($html, $encoding = "7bit", $disposition = "inline")
  {
    $this->bodyHtml = new Sabel_Mail_Body($html, Sabel_Mail_Body::HTML);
    $this->bodyHtml->setEncoding($encoding);
    $this->bodyHtml->setDisposition($disposition);
    
    return $this;
  }
  
  public function attach($fileName, $data, $mimeType, $encoding = "base64", $disposition = "attachment")
  {
    // @todo RFC2231
    $attachment = new Sabel_Mail_File($this->encodeHeader($fileName), $data, $mimeType);
    $attachment->setEncoding($encoding);
    $attachment->setDisposition($disposition);
    $this->attachments[] = $attachment;
    
    return $this;
  }
  
  public function addHeader($name, $value)
  {
    $this->headers[$name] = $value;
    
    return $this;
  }
  
  public function getHeader($name)
  {
    if (isset($this->headers[$name])) {
      return $this->headers[$name];
    } else {
      return null;
    }
  }
  
  public function getHeaders()
  {
    return $this->headers;
  }
  
  public function encodeHeader($header)
  {
    if ($this->isMbstringLoaded) {
      return mb_encode_mimeheader($header, $this->charset);
    } else {
      $quoted = Sabel_Mail_QuotedPrintable::encode($header, self::LINELENGTH, self::$EOL);
      $quoted = str_replace(array("?", " "), array("=3F", "=20"), $quoted);
      return "=?{$this->charset}?Q?{$quoted}?=";
    }
  }
  
  public function send(array $options = array())
  {
    if ($this->sender === null) {
      $this->sender = new Sabel_Mail_Sender_PHP();
    }
    
    $bodyText = $this->createBodyText();
    return $this->sender->send($this->headers, $bodyText, $options);
  }
  
  protected function createBodyText()
  {
    $boundary = $this->getBoundary();
    
    if ($this->bodyText !== null && $this->bodyHtml !== null) {
      $this->headers["Content-Type"] = 'multipart/alternative; boundary="' . $boundary . '"';
      $text = $this->bodyText->getText();
      $html = $this->bodyHtml->getText();
      
      if ($this->isMbstringLoaded) {
        $text = mb_convert_encoding($text, $this->charset);
        $html = mb_convert_encoding($html, $this->charset);
      }
      
      $body   = array();
      $body[] = "--{$boundary}";
      $body[] = $this->createBodyHeader($this->bodyText) . self::$EOL;
      $body[] = $text . self::$EOL;
      $body[] = "--{$boundary}";
      $body[] = $this->createBodyHeader($this->bodyHtml) . self::$EOL;
      $body[] = $html . self::$EOL;
      $body[] = "--{$boundary}--";
      
      return implode(self::$EOL, $body);
    }
    
    if ($this->bodyText === null && $this->bodyHtml === null) {
      $bodyObj = new Sabel_Mail_Body("", Sabel_Mail_Body::TEXT);
    } elseif ($this->bodyText !== null) {
      $bodyObj = $this->bodyText;
    } else {
      $bodyObj = $this->bodyHtml;
    }
    
    if ($this->isMbstringLoaded) {
      $bodyObj->setText(mb_convert_encoding($bodyObj->getText(), $this->charset));
    }
    
    if (count($this->attachments) === 0) {
      $this->headers["Content-Type"] = $bodyObj->getType() . "; charset=" . $this->charset;
      return $bodyObj->getText();
    } else {
      $this->headers["Content-Type"] = 'multipart/mixed; boundary="' . $boundary . '"';
      
      $body   = array();
      $body[] = "--{$boundary}";
      $body[] = $this->createBodyHeader($bodyObj) . self::$EOL;
      $body[] = $bodyObj->getText() . self::$EOL;
      
      foreach ($this->attachments as $attachment) {
        $name = $attachment->getName();
        $data = $attachment->getData();
        $encoding = strtolower($attachment->getEncoding());
        
        if ($encoding === "base64") {
          $data = rtrim(chunk_split(base64_encode($data), self::LINELENGTH, self::$EOL));
        } elseif ($encoding === "quoted-printable") {
          $quoted = Sabel_Mail_QuotedPrintable::encode($data, self::LINELENGTH, self::$EOL);
          $quoted = str_replace(array("?", " "), array("=3F", "=20"), $quoted);
          $data   = "=?{$this->charset}?Q?{$quoted}?=";
        } else {
          $message = __METHOD__ . "() invalid encoding";
          throw new Sabel_Mail_Exception($message);
        }
        
        $body[] = "--{$boundary}";
        $body[] = "Content-Disposition: " . $attachment->getDisposition() . "; filename=\"{$name}\"";
        $body[] = "Content-Transfer-Encoding: {$encoding}";
        $body[] = "Content-Type: " . $attachment->getType() . "; name=\"{$name}\"" . self::$EOL;
        $body[] = $data . self::$EOL;
      }
      
      $body[] = "--{$boundary}--";
      return implode(self::$EOL, $body);
    }
  }
  
  protected function createBodyHeader($body)
  {
    return "Content-Disposition: " . $body->getDisposition()    . self::$EOL
         . "Content-Transfer-Encoding: " . $body->getEncoding() . self::$EOL
         . "Content-Type: " . $body->getType() . "; charset=" . $this->charset;
  }
}
