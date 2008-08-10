<?php

/**
 * Sabel_Db_Validate_Config_Model
 *
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Db_Validate_Config_Model
{
  /**
   * @var Sabel_Db_Validate_Config_Column[]
   */
  private $columns = array();
  
  /**
   * @param string $colName
   *
   * @return Sabel_Db_Validate_Config_Column
   */
  public function column($colName)
  {
    if (!isset($this->columns[$colName])) {
      $this->columns[$colName] = new Sabel_Db_Validate_Config_Column();
    }
    
    return $this->columns[$colName];
  }
  
  /**
   * @return Sabel_Db_Validate_Config_Column[]
   */
  public function getColumns()
  {
    return $this->columns;
  }
}
