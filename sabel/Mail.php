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
   * @var array
   */
  protected $recipients = array();
  
  /**
   * @var string
   */
  protected $subject = "";
  
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
  
  public function addRecipient($recipient, $name = "")
  {
    if ($name === "") {
      $this->recipients[] = $recipient;
    } else {
      $this->recipients[] = $this->encodeHeader($name) . " <{$recipient}>";
    }
    
    return $this;
  }
  
  public function getRecipients()
  {
    return $this->recipients;
  }
  
  public function addCc($to, $name = "")
  {
    if ($name === "") {
      $this->headers["Cc"] = $to;
    } else {
      $this->headers["Cc"] = $this->encodeHeader($name) . " <{$to}>";
    }
    
    return $this;
  }
  
  public function addBcc($to, $name = "")
  {
    if ($name === "") {
      $this->headers["Bcc"] = $to;
    } else {
      $this->headers["Bcc"] = $this->encodeHeader($name) . " <{$to}>";
    }
    
    return $this;
  }
  
  public function setSubject($subject)
  {
    $this->subject = $subject;
    
    return $this;
  }
  
  public function getSubject()
  {
    return $this->subject;
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
  
  public function attach($fileName, $data, $mimeType, $encoding = "base64", $disposition = "inline")
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
  
  public function send($parameters = "")
  {
    $bodyText = $this->createBodyText();
    $subject  = $this->encodeHeader($this->getSubject());
    $headers  = $this->createHeader();
    
    $to = implode(", ", $this->recipients);
    return mail($to, $subject, $bodyText, $headers, $parameters);
  }
  
  protected function createHeader()
  {
    $headers = array();
    $hasMimeVersion = false;
    
    foreach ($this->headers as $name => $header) {
      $lowered = strtolower($name);
      if ($lowered === "mime-version") $hasMimeVersion = true;
      
      $headers[] = $name . ": " . $header;
    }
    
    if (!$hasMimeVersion) {
      $headers[] = "MIME-Version: 1.0";
    }
    
    return implode("\r\n", $headers);
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
          $lineLength = 72; // @todo to constant value
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
