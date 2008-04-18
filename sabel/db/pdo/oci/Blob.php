<?php

/**
 * Sabel_DB_Pdo_Oci_Blob
 *
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_DB_Pdo_Oci_Blob extends Sabel_DB_Pdo_Blob
{
  public function __construct($binary)
  {
    $this->binary = $binary;
  }
  
  public function getEscapedContents()
  {
    $filePath = sys_get_temp_dir() . DS . md5(uniqid(mt_rand(), true));
    file_put_contents($filePath, $this->binary);
    return fopen($filePath, "rb");
  }
}
