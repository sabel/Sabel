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
  
  public function __construct($charset = "ISO-8859-1")
  {
    $this->charset = $charset;
    $this->isMbstringLoaded = extension_loaded("mbstring");
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
  
  public function addCc($to, $name = "")
  {
    if ($name !== "") {
      $to = $this->encodeHeader($name) . " <{$to}>";
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
      $quoted = Sabel_Mail_QuotedPrintable::encode($header, self::LINELENGTH, "\r\n");
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
      
      $body = "--{$boundary}\r\n"
            . "Content-Disposition: " . $this->bodyText->getDisposition()    . "\r\n"
            . "Content-Transfer-Encoding: " . $this->bodyText->getEncoding() . "\r\n"
            . "Content-Type: text/plain; charset=" . $this->charset          . "\r\n"
            . "\r\n"
            . $text
            . "\r\n\r\n"
            . "--{$boundary}\r\n"
            . "Content-Disposition: " . $this->bodyHtml->getDisposition()    . "\r\n"
            . "Content-Transfer-Encoding: " . $this->bodyHtml->getEncoding() . "\r\n"
            . "Content-Type: text/html; charset=" . $this->charset           . "\r\n"
            . "\r\n"
            . $html
            . "\r\n\r\n"
            . "--{$boundary}--";
      
      return $body;
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
      
      $body = "--{$boundary}\r\n"
            . "Content-Disposition: " . $bodyObj->getDisposition()    . "\r\n"
            . "Content-Transfer-Encoding: " . $bodyObj->getEncoding() . "\r\n"
            . "Content-Type: " . $bodyObj->getType() . "; charset=" . $this->charset . "\r\n"
            . "\r\n"
            . $bodyObj->getText()
            . "\r\n\r\n";
      
      foreach ($this->attachments as $attachment) {
        $name = $attachment->getName();
        $data = $attachment->getData();
        $encoding = strtolower($attachment->getEncoding());
        
        if ($encoding === "base64") {
          $data = rtrim(chunk_split(base64_encode($data), self::LINELENGTH, "\r\n"));
        } elseif (preg_match('/^quoted-?printable$/', $encoding) === 1) {
          $quoted = Sabel_Mail_QuotedPrintable::encode($data, self::LINELENGTH, "\r\n");
          $quoted = str_replace(array("?", " "), array("=3F", "=20"), $quoted);
          $data   = "=?{$this->charset}?Q?{$quoted}?=";
        }
        
        $body .= "--{$boundary}\r\n"
               . "Content-Disposition: " . $attachment->getDisposition() . "; filename=\"{$name}\"\r\n"
               . "Content-Transfer-Encoding: {$encoding}\r\n"
               . "Content-Type: " . $attachment->getType() . "; name=\"{$name}\"\r\n"
               . "\r\n"
               . $data
               . "\r\n\r\n";
      }
      
      return $body . "--{$boundary}--";
    }
  }
}
