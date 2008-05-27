<?php

/**
 * Sabel_Mail_Part
 *
 * @abstract
 * @category   Mail
 * @package    org.sabel.mail
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
abstract class Sabel_Mail_Part extends Sabel_Object
{
  /**
   * @var string
   */
  protected $charset = "ISO-8859-1";
  
  /**
   * @var string
   */
  protected $encoding = "7bit";
  
  /**
   * @var string
   */
  protected $disposition = "inline";
  
  /**
   * @param string $charset
   *
   * @return void
   */
  public function setCharset($charset)
  {
    $this->charset = $charset;
  }
  
  /**
   * @return string
   */
  public function getCharset()
  {
    return $this->charset;
  }
  
  /**
   * @param string $encoding
   *
   * @return void
   */
  public function setEncoding($encoding)
  {
    $this->encoding = strtolower($encoding);
  }
  
  /**
   * @return string
   */
  public function getEncoding()
  {
    return $this->encoding;
  }
  
  /**
   * @param string $disposition
   *
   * @return void
   */
  public function setDisposition($disposition)
  {
    $this->disposition = strtolower($disposition);
  }
  
  /**
   * @return string
   */
  public function getDisposition()
  {
    return $this->disposition;
  }
}
