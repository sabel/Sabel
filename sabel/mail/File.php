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
  
  public function __construct($name, $data, $type)
  {
    $this->name = $name;
    $this->data = $data;
    $this->type = $type;
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
}
