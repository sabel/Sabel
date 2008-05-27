<?php

/**
 * Sabel_Mail_File
 *
 * @category   Mail
 * @package    org.sabel.mail
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Mail_File extends Sabel_Mail_Part
{
  /**
   * @var string
   */
  protected $name = "";
  
  /**
   * @var string
   */
  protected $data = "";
  
  /**
   * @var string
   */
  protected $type = "";
  
  /**
   * @var boolean
   */
  protected $followRFC2231 = false;
  
  public function __construct($name, $data, $type, $followRFC2231 = false)
  {
    $this->name = $name;
    $this->data = $data;
    $this->type = $type;
    $this->followRFC2231 = $followRFC2231;
  }
  
  /**
   * @param string $name
   *
   * @return void
   */
  public function setName($name)
  {
    $this->name= $name;
  }
  
  /**
   * @return string
   */
  public function getName()
  {
    return $this->name;
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
   * @param string $data
   *
   * @return void
   */
  public function setData($data)
  {
    $this->data = $data;
  }
  
  /**
   * @return string
   */
  public function getData()
  {
    return $this->data;
  }
  
  /**
   * @return string
   */
  public function toMailPart()
  {
    $name = $this->name;
    $data = $this->data;
    $eol  = Sabel_Mail::getEol();
    
    $encoding = $this->encoding;
    $disposition = $this->disposition;
    
    if ($encoding === "base64") {
      $data = rtrim(chunk_split(base64_encode($data), Sabel_Mail::LINELENGTH, $eol));
    } elseif ($encoding === "quoted-printable") {
      $quoted = Sabel_Mail_QuotedPrintable::encode($data, Sabel_Mail::LINELENGTH, $eol);
      $data   = str_replace(array("?", " "), array("=3F", "=20"), $quoted);
    } else {
      $message = __METHOD__ . "() invalid encoding.";
      throw new Sabel_Mail_Exception($message);
    }
    
    $part = array();
    
    if ($disposition === "inline") {
      $part[] = "Content-Type: {$this->type}";
      $part[] = "Content-Transfer-Encoding: {$encoding}";
      $part[] = "Content-Id: <{$name}>";
      $part[] = $eol . $data . $eol;
    } elseif ($this->followRFC2231) {
      $part[] = "Content-Type: " . $this->type;
      $part[] = "Content-Disposition: " . $this->disposition . ";";
      $part[] = $this->toRFC2231($name, $eol);
      $part[] = "Content-Transfer-Encoding: {$encoding}";
      $part[] = $eol . $data . $eol;
    } else {
      if (extension_loaded("mbstring")) {
        $name = mb_encode_mimeheader($name, $this->charset);
      } else {
        $name = "=?{$this->charset}?B?" . base64_encode($name) . "?=";
      }
      
      $part[] = "Content-Type: " . $this->type . "; name=\"{$name}\"";
      $part[] = "Content-Disposition: " . $this->disposition . "; filename=\"{$name}\"";
      $part[] = "Content-Transfer-Encoding: {$encoding}";
      $part[] = $eol . $data . $eol;
    }
    
    return implode($eol, $part);
  }
  
  protected function toRFC2231($name, $eol)
  {
    if (extension_loaded("mbstring")) {
      $name = mb_convert_encoding($name, $this->charset);
    }
    
    $exploded = explode("%", urlencode($name));
    array_shift($exploded);
    $blocks = array();
    for ($i = 0; $i < 1000; $i++) {
      $res = array_slice($exploded, $i * 13, 13);
      if (empty($res)) break;
      $blocks[] = $res;
    }
    
    if (count($blocks) === 1) {
      return " filename*0*={$this->charset}''%" . implode("%", $blocks[0]);
    } else {
      $last  = array_pop($blocks);
      $names = array();
      foreach ($blocks as $i => $block) {
        if ($i === 0) {
          $names[] = " filename*{$i}*={$this->charset}''%" . implode("%", $block) . ";";
        } else {
          $names[] = " filename*{$i}*=%" . implode("%", $block) . ";";
        }
      }
      
      $names[] = " filename*" . ++$i . "*=%" . implode("%", $last);
      return implode($eol, $names);
    }
  }
}
