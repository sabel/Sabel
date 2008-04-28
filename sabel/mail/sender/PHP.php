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
    $recipients = array();
    if (isset($headers["To"])) {
      $to = $headers["To"];
      unset($headers["To"]);
      foreach ($to as $recipient) {
        $recipients[] = $recipient;
      }
    }
    
    $subject = "";
    if (isset($headers["Subject"])) {
      $subject = $headers["Subject"];
      unset($headers["Subject"]);
    }
    
    $recipients  = implode(", ", $recipients);
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
      
      if (is_array($header)) {
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
}
