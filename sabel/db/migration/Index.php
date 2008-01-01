<?php

/**
 * Sabel_DB_Migration_Index
 *
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_DB_Migration_Index
{
  private
    $create = array(),
    $drop   = array();
    
  public function create($colName)
  {
    if (is_string($colName)) {
      $this->create[] = $colName;
    } else {
      Sabel_Command::error("argument must be a string.");
      exit;
    }
  }
  
  public function drop($colName)
  {
    if (is_string($colName)) {
      $this->drop[] = $colName;
    } else {
      Sabel_Command::error("argument must be a string.");
      exit;
    }
  }
  
  public function getCreateIndexes()
  {
    return $this->create;
  }
  
  public function getDropIndexes()
  {
    return $this->drop;
  }
}
