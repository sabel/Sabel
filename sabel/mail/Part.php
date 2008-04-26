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
  protected $encoding = "7bit";
  
  /**
   * @var string
   */
  protected $disposition = "inline";
  
  /**
   * @param string $encoding
   *
   * @return void
   */
  public function setEncoding($encoding)
  {
    $this->encoding = $encoding;
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
    $this->disposition = $disposition;
  }
  
  /**
   * @return string
   */
  public function getDisposition()
  {
    return $this->disposition;
  }
}
