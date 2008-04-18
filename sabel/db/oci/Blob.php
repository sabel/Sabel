<?php

/**
 * Sabel_DB_Oci_Blob
 *
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_DB_Oci_Blob extends Sabel_DB_Abstract_Blob
{
  protected $conn = null;
  protected $lob  = null;
  
  public function __construct($conn, $binary)
  {
    $this->conn   = $conn;
    $this->binary = $binary;
    $this->lob    = oci_new_descriptor($conn, OCI_D_LOB);
  }
  
  public function getEscapedContents()
  {
    return $this->binary;
  }
  
  public function getLob()
  {
    return $this->lob;
  }
  
  public function save()
  {
    $this->lob->save($this->getEscapedContents());
  }
}
