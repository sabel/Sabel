<?php

/**
 * Sabel_Mail_Sender_Smtp
 *
 * @category   Mail
 * @package    org.sabel.mail
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Mail_Sender_Smtp
  extends Sabel_Object implements Sabel_Mail_Sender_Interface
{
  /**
   * @var string
   */
  protected $server = "localhost";
  
  /**
   * @var string
   */
  protected $port = "25";
  
  public function __construct($server = "localhost", $port = "25")
  {
    $this->server = $server;
    $this->port   = $port;
  }
  
  public function send(array $headers, $body, $options = array())
  {
    $rcptTo = "";
    if (isset($headers["To"])) {
      $rcptTo = $headers["To"][0];
    } else {
      $message = "";
      throw new Sabel_Exception_Runtime($message);
    }
    
    if (!isset($headers["MIME-Version"])) {
      $headers["MIME-Version"] = "1.0";
    }
    
    $smtp = fsockopen($this->server, $this->port);
    
    fputs($smtp,"HELO $server\r\n");
    fputs($smtp,"MAIL FROM:<{$headers["From"]}>\r\n");
    fputs($smtp,"RCPT TO:<$rcptTo>\r\n");
    fputs($smtp, "DATA\r\n");
    
    foreach ($headers as $name => $header) {
      if (is_array($header)) {
        foreach ($header as $value) {
          fputs($smtp, "{$name}: {$value}\r\n");
        }
      } else {
        fputs($smtp, "{$name}: {$header}\r\n");
      }
    }
    
    fputs($smtp, "\r\n$body\r\n");
    fputs($smtp, "\r\n.\r\n");
    
    $result = fgets($smtp);
    fclose($smtp);
    return (preg_match("/^250/", $result) === 1);
  }
}
