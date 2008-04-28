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
  
  /**
   * @var resource
   */
  protected $smtp = null;
  
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
    
    $this->connect();
    
    $this->command("EHLO {$this->server}");
    while ($result = trim(fgets($this->smtp))) {
      if (strpos($result, "250") !== 0) return false;
      if (strpos($result, "250 ") === 0) break;
    }
    
    if ($result === false) return false;
    
    if (isset($options["auth"])) {
      $method = strtolower($options["auth"]) . "Auth";
      $this->$method($options["user"], $options["password"]);
    }
    
    $this->command("MAIL FROM:<{$headers["From"]}>", "250");
    
    try {
      $this->command("RCPT TO:<{$rcptTo}>", "250");
    } catch (Sabel_Mail_Smtp_Exception $e) {
      $exception = new Sabel_Mail_Smtp_Exception_RecipientRefused($e->getMessage());
      $exception->setResponseCode($e->getResponseCode());
      throw $exception;
    }

    $this->command("DATA", "354");
    
    foreach ($headers as $name => $header) {
      if (is_array($header)) {
        foreach ($header as $value) {
          $this->command("{$name}: {$value}");
        }
      } else {
        $this->command("{$name}: {$header}");
      }
    }
    
    $this->command("\r\n$body");
    $this->command("\r\n.", "250");
    
    fclose($this->smtp);
  }
  
  protected function connect()
  {
    $this->smtp = fsockopen($this->server, $this->port);
    
    if (strpos(rtrim(fgets($this->smtp)), "220") !== 0) {
      $message = "can't connect to the SMTP Server. "
               . "HOST => '{$this->server}, PORT => '{$this->port}'";
      
      throw new Sabel_Mail_Smtp_Exception_ConnectionRefused($message);
    }
  }
  
  protected function plainAuth($user, $password)
  {
    try {
      $command = "AUTH PLAIN " . base64_encode("{$user}\000{$user}\000{$password}");
      $this->command($command, "235");
    } catch (Sabel_Mail_Smtp_Exception $e) {
      $exception = new Sabel_Mail_Smtp_Exception_AuthFailure($e->getMessage());
      $exception->setResponseCode($e->getResponseCode());
      throw $exception;
    }
  }
  
  protected function loginAuth($user, $password)
  {
    try {
      $this->command("AUTH LOGIN", "334");
      $this->command(base64_encode($user), "334");
      $this->command(base64_encode($password), "235");
    } catch (Sabel_Mail_Smtp_Exception $e) {
      $exception = new Sabel_Mail_Smtp_Exception_AuthFailure($e->getMessage());
      $exception->setResponseCode($e->getResponseCode());
      throw $exception;
    }
  }
  
  protected function command($command, $expectedStatus = null)
  {
    fputs($this->smtp, $command . "\r\n");
    
    if ($expectedStatus === null) {
      return true;
    } else {
      $result = rtrim(fgets($this->smtp));
      preg_match("/^[0-9]+/", $result, $matches);
      
      if ($matches[0] === $expectedStatus) {
        return true;
      } else {
        $message = "got unexpected response code '{$matches[0]}'.";
        $exception = new Sabel_Mail_Smtp_Exception($message);
        $exception->setResponseCode($matches[0]);
        throw $exception;
      }
    }
  }
}
