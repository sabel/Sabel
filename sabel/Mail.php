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
  protected $headerEncoding = "base64";
  
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
  
  public function setHeaderEncoding($encoding)
  {
    $this->headerEncoding = strtolower($encoding);
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
    $this->bodyText->setCharset($this->charset);
    $this->bodyText->setEncoding($encoding);
    $this->bodyText->setDisposition($disposition);
    
    return $this;
  }
  
  public function setBodyHtml($html, $encoding = "7bit", $disposition = "inline")
  {
    $this->bodyHtml = new Sabel_Mail_Body($html, Sabel_Mail_Body::HTML);
    $this->bodyHtml->setEncoding($encoding);
    $this->bodyHtml->setDisposition($disposition);
    $this->bodyHtml->setCharset($this->charset);
    
    return $this;
  }
  
  public function attach($fileName, $data, $mimeType,
                         $encoding = "base64", $disposition = "attachment", $followRFC2231 = false)
  {
    $attachment = new Sabel_Mail_File($fileName, $data, $mimeType, $followRFC2231);
    $attachment->setEncoding($encoding);
    $attachment->setDisposition($disposition);
    $attachment->setCharset($this->charset);
    
    $this->attachments[] = $attachment;
    
    return $this;
  }
  
  public function generateContentId()
  {
    list (, $host) = explode("@", $this->headers["From"]["address"]);
    return md5hash() . "@" . $host;
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
      $enc = ($this->headerEncoding === "base64") ? "B" : "Q";
      return mb_encode_mimeheader($header, $this->charset, $enc);
    } elseif ($this->headerEncoding === "base64") {
      return "=?{$this->charset}?B?" . base64_encode($header) . "?=";
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
    
    $hasMessageId  = false;
    $hasMimeHeader = false;
    
    foreach ($this->headers as $name => $header) {
      $lowered = strtolower($name);
      if ($lowered === "message-id")   $hasMessageId  = true;
      if ($lowered === "mime-version") $hasMimeHeader = true;
    }
    
    if (!$hasMessageId) {
      list (, $host) = explode("@", $this->headers["From"]["address"]);
      $this->headers["Message-Id"] = "<" . md5hash() . "@{$host}>";
    }
    
    if (!$hasMimeHeader) {
      $this->headers["Mime-Version"] = "1.0";
    }
    
    $bodyText = $this->createBodyText();
    return $this->sender->send($this->headers, $bodyText, $options);
  }
  
  protected function createBodyText()
  {
    // empty body.
    if ($this->bodyText === null && $this->bodyHtml === null) {
      $message = __METHOD__ . "() empty body.";
      throw new Sabel_Mail_Exception($message);
    }
    
    $boundary  = $this->getBoundary();
    $boundary2 = md5hash();
    $body = array("--{$boundary}");
    
    list ($hasAttachment, $hasInlineContent) = $this->_setContentType($boundary);
    
    if ($this->bodyText !== null && $this->bodyHtml !== null) {  // plain & html texts.
      
      if ($hasAttachment && $hasInlineContent) {
        $boundary3 = md5hash();
        $body[] = 'Content-Type: multipart/alternative; boundary="' . $boundary2 . '"' . self::$EOL;
        $body[] = "--{$boundary2}";
        $body[] = $this->bodyText->toMailPart();
        $body[] = "--{$boundary2}";
        $body[] = 'Content-Type: multipart/related; boundary="' . $boundary3 . '"' . self::$EOL;
        $body[] = "--{$boundary3}";
        $body[] = $this->bodyHtml->toMailPart();
        $body[] = $this->createAttachmentText($boundary3, "inline");
        $body[] = "--{$boundary3}--" . self::$EOL;
        $body[] = "--{$boundary2}--" . self::$EOL;
        $body[] = $this->createAttachmentText($boundary, "attachment");
      } elseif ($hasInlineContent) {
        $body[] = $this->bodyText->toMailPart();
        $body[] = "--{$boundary}";
        $body[] = 'Content-Type: multipart/related; boundary="' . $boundary2 . '"' . self::$EOL;
        $body[] = "--{$boundary2}";
        $body[] = $this->bodyHtml->toMailPart();
        $body[] = $this->createAttachmentText($boundary2, "inline");
        $body[] = "--{$boundary2}--" . self::$EOL;
      } elseif ($hasAttachment) {
        $body[] = 'Content-Type: multipart/alternative; boundary="' . $boundary2 . '"' . self::$EOL;
        $body[] = "--{$boundary2}";
        $body[] = $this->bodyText->toMailPart();
        $body[] = "--{$boundary2}";
        $body[] = $this->bodyHtml->toMailPart();
        $body[] = "--{$boundary2}--" . self::$EOL;
        $body[] = $this->createAttachmentText($boundary, "attachment");
      } else {
        $body[] = $this->bodyText->toMailPart();
        $body[] = "--{$boundary}";
        $body[] = $this->bodyHtml->toMailPart();
      }
      
      $body[] = "--{$boundary}--";
      return implode(self::$EOL, $body);
    } elseif ($this->bodyHtml !== null) {  // only html text.
      if ($hasAttachment && $hasInlineContent) {
        $body[] = 'Content-Type: multipart/related; boundary="' . $boundary2 . '"' . self::$EOL;
        $body[] = "--{$boundary2}";
        $body[] = $this->bodyHtml->toMailPart();
        $body[] = $this->createAttachmentText($boundary2, "inline");
        $body[] = "--{$boundary2}--" . self::$EOL;
        $body[] = $this->createAttachmentText($boundary, "attachment");
      } else {
        $body[] = $this->bodyHtml->toMailPart();
        
        if ($hasInlineContent) {
          $body[] = $this->createAttachmentText($boundary, "inline");
        } elseif ($hasAttachment) {
          $body[] = $this->createAttachmentText($boundary, "attachment");
        }
      }
      
      $body[] = "--{$boundary}--";
      return implode(self::$EOL, $body);
    } else {  // only plain text.
      if ($hasAttachment) {
        $body   = array("--{$boundary}");
        $body[] = $this->bodyText->toMailPart();
        $body[] = $this->createAttachmentText($boundary, "attachment");
        $body[] = "--{$boundary}--";
        
        return implode(self::$EOL, $body);
      } else {
        $this->headers["Content-Transfer-Encoding"] = $this->bodyText->getEncoding();
        return $this->bodyText->getEncodedText();
      }
    }
  }
  
  protected function _setContentType($boundary)
  {
    $hasAttachment = false;
    $hasInlineContent = false;
    
    if (count($this->attachments) > 0) {
      foreach ($this->attachments as $attachment) {
        $disposition = $attachment->getDisposition();
        if ($disposition === "attachment") {
          $hasAttachment = true;
        } elseif ($disposition === "inline") {
          $hasInlineContent = true;
        }
      }
    }
    
    if ($hasAttachment) {
      $this->headers["Content-Type"] = 'multipart/mixed; boundary="' . $boundary . '"';
    } elseif ($this->bodyText !== null && $this->bodyHtml !== null) {
      $this->headers["Content-Type"] = 'multipart/alternative; boundary="' . $boundary . '"';
    } elseif ($this->bodyHtml !== null && $hasInlineContent) {
      $this->headers["Content-Type"] = 'multipart/related; boundary="' . $boundary . '"';
    } else {
      $body = ($this->bodyText !== null) ? $this->bodyText : $this->bodyHtml;
      $this->headers["Content-Type"] = $body->getType() . "; charset=" . $this->charset;
    }
    
    return array($hasAttachment, $hasInlineContent);
  }
  
  protected function createAttachmentText($boundary, $disposition = "attachment")
  {
    $texts = array();
    foreach ($this->attachments as $attachment) {
      if ($attachment->getDisposition() === $disposition) {
        $texts[] = "--{$boundary}";
        $texts[] = $attachment->toMailPart();
      }
    }
    
    return implode(self::$EOL, $texts);
  }
}
