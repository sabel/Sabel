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
  protected $files = array();
  
  public function __construct($charset = "ISO-8859-1")
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
      $this->headers["From"] = $from;
    } else {
      $this->headers["From"] = $this->encodeHeader($name) . " <{$from}>";
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
    if ($name !== "") {
      $to = $this->encodeHeader($name) . " <{$to}>";
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
  
  public function getBodyText()
  {
    return $this->bodyText;
  }
  
  public function setBodyHtml($html, $encoding = "7bit", $disposition = "inline")
  {
    $this->bodyHtml = new Sabel_Mail_Body($html, Sabel_Mail_Body::HTML);
    $this->bodyHtml->setEncoding($encoding);
    $this->bodyHtml->setDisposition($disposition);
    
    return $this;
  }
  
  public function getBodyHtml()
  {
    return $this->bodyHtml;
  }
  
  public function attach($fileName, $data, $mimeType, $encoding = "base64", $disposition = "attachment")
  {
    $file = new Sabel_Mail_File($fileName, $data, $mimeType);
    $file->setEncoding($encoding);
    $file->setDisposition($disposition);
    
    $this->files[] = $file;
    
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
    if (extension_loaded("mbstring")) {
      return mb_encode_mimeheader($header, $this->charset);
    } else {
      // @todo
      return $header;
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
    $mbstringLoaded = extension_loaded("mbstring");
    $boundary = $this->getBoundary();
    
    if ($this->bodyText !== null && $this->bodyHtml !== null) {
      $this->headers["Content-Type"] = 'multipart/alternative; boundary="' . $boundary . '"';
      $text = $this->bodyText->getText();
      $html = $this->bodyHtml->getText();
      
      if ($mbstringLoaded) {
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
    
    if ($mbstringLoaded) {
      $bodyObj->setText(mb_convert_encoding($bodyObj->getText(), $this->charset));
    }
    
    if (count($this->files) === 0) {
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
      
      foreach ($this->files as $file) {
        $name = $file->getName();
        $data = $file->getData();
        $encoding = strtolower($file->getEncoding());
        
        if ($encoding === "base64") {
          $lineLength = 74; // @todo to constant value
          $data = rtrim(chunk_split(base64_encode($data), $lineLength, "\r\n"));
        }
        
        $body .= "--{$boundary}\r\n"
               . "Content-Disposition: " . $file->getDisposition() . "; filename=\"{$name}\"\r\n"
               . "Content-Transfer-Encoding: {$encoding}\r\n"
               . "Content-Type: " . $file->getType() . "; name=\"{$name}\"\r\n"
               . "\r\n"
               . $data
               . "\r\n\r\n";
      }
      
      return $body . "--{$boundary}--";
    }
  }
}
