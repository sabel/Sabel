<?php

/**
 * Sabel_Mail_Sender_PHP
 *
 * @category   Mail
 * @package    org.sabel.mail
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Mail_Sender_PHP
  extends Sabel_Object implements Sabel_Mail_Sender_Interface
{
  public function send(array $headers, $body, $options = array())
  {
    $recipients  = $this->getRecipients($headers);
    $subject     = $this->getSubject($headers);
    $headersText = implode("\r\n", $this->createHeaderText($headers));
    
    if (isset($options["parameters"])) {
      return mail($recipients, $subject, $body, $headersText, $options["parameters"]);
    } else {
      return mail($recipients, $subject, $body, $headersText);
    }
  }
  
  protected function createHeaderText($headersArray)
  {
    $headers = array();
    $hasMimeVersion = false;
    
    foreach ($headersArray as $name => $header) {
      $lowered = strtolower($name);
      if ($lowered === "mime-version") $hasMimeVersion = true;
      
      if ($name === "From") {
        if ($header["name"] === "") {
          $headers[] = "From: <{$header["address"]}>";
        } else {
          $headers[] = "From: {$header["name"]} <{$header["address"]}>";
        }
      } elseif (is_array($header)) {
        foreach ($header as $value) {
          $headers[] = $name . ": " . $value;
        }
      } else {
        $headers[] = $name . ": " . $header;
      }
    }
    
    if (!$hasMimeVersion) {
      $headers[] = "MIME-Version: 1.0";
    }
    
    return $headers;
  }
  
  protected function getRecipients(&$headers)
  {
    $recipients = array();
    if (isset($headers["To"])) {
      $to = $headers["To"];
      unset($headers["To"]);
      foreach ($to as $recipient) {
        if ($recipient["name"] === "") {
          $recipients[] = $recipient["address"];
        } else {
          $recipients[] = $recipient["name"] . " <{$recipient["address"]}>";
        }
      }
      
      return implode(", ", $recipients);
    } else {
      $message = __METHOD__ . "() empty recipients.";
      throw new Sabel_Mail_Exception($message);
    }
  }
  
  protected function getSubject(&$headers)
  {
    $subject = "";
    if (isset($headers["Subject"])) {
      $subject = $headers["Subject"];
      unset($headers["Subject"]);
    }
    
    return $subject;
  }
}
