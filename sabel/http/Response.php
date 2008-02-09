<?php

/**
 * HTTP Response
 *
 * @category   Http
 * @package    org.sabel.http
 * @author     Mori Reo <mori.reo@sabel.jp>
 * @copyright  2002-2006 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Http_Response extends Sabel_Object
{
  protected $header = null;
  protected $contents = array();
  
  public function __get($name)
  {
    return ($name === 'header') ? $this->header : null;
  }
  
  /**
   * set header object
   *
   */
  public function setHeader($header)
  {
    if (!$header instanceof Sabel_Http_Header)
      throw new Sabel_Exception_Runtime($header . " is not Sabel_Http_Header");
      
    $this->header = $header;
  }
  
  /**
   * get header object
   *
   * @return Sabel_Http_Header
   */
  public function getHeader()
  {
    return $this->header;
  }
  
  public function setContents($contents)
  {
    $this->contents = $contents;
  }
  
  public function getContents()
  {
    return $this->contents;
  }
  
  public function getContentsByArray()
  {
    return explode("\n", $this->contents);
  }
  
  public function hasContents()
  {
    return (empty($this->contents));
  }
  
  /**
   * simple delegate
   *
   * @return boolean
   */
  public function isInformation()
  {
    return $this->header->isInformation();
  }
  
  /**
   * simple delegate
   *
   * @return boolean
   */
  public function isSuccess()
  {
    return $this->header->isSuccess();
  }
  
  /**
   * simple delegate
   *
   * @return boolean
   */
  public function isForward()
  {
    return $this->header->isForward();
  }
  
  /**
   * simple delegate
   *
   * @return boolean
   */
  public function isClientError()
  {
    return $this->header->isClientError();
  }
  
  /**
   * simple delegate
   *
   * @return boolean
   */
  public function isServerError()
  {
    return $this->header->isServerError();
  }
}
