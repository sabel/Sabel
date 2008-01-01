<?php

/**
 * Sabel_DB_Migration_Reader
 *
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_DB_Migration_Reader extends Sabel_Object
{
  private $filePath = "";
  
  public function __construct($filePath)
  {
    $this->filePath = $filePath;
  }
  
  public function readCreate()
  {
    $create = new Sabel_DB_Migration_Create();
    include ($this->filePath);
    return $create->build();
  }
  
  public function readAddColumn()
  {
    $add = new Sabel_DB_Migration_AddColumn();
    include ($this->filePath);
    return $add->build();
  }
  
  public function readDropColumn()
  {
    $drop = new Sabel_DB_Migration_DropColumn();
    include ($this->filePath);
    return $drop;
  }
  
  public function readChangeColumn()
  {
    $change = new Sabel_DB_Migration_ChangeColumn();
    include ($this->filePath);
    return $change;
  }
  
  public function readIndex()
  {
    $index = new Sabel_DB_Migration_Index();
    include ($this->filePath);
    return $index;
  }
  
  public function readQuery()
  {
    $query = new Sabel_DB_Migration_Query();
    include ($this->filePath);
    return $query;
  }
}
