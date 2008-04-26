<?php

/**
 * Sabel_Mail_Body
 *
 * @category   Mail
 * @package    org.sabel.mail
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Mail_Body extends Sabel_Mail_Part
{
  const TEXT = "text/plain";
  const HTML = "text/html";
  
  /**
   * @var string
   */
  protected $text = "";
  
  /**
   * @var string
   */
  protected $type = self::TEXT;
  
  public function __construct($text, $type = self::TEXT)
  {
    $this->text = $text;
    $this->type = $type;
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
   * @param string $text
   *
   * @return void
   */
  public function setText($text)
  {
    $this->text = $text;
  }
  
  /**
   * @return string
   */
  public function getText()
  {
    return $this->text;
  }
}
