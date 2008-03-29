<?php

/**
 * Sabel_DB_Pdo_Statement
 *
 * @abstract
 * @category   DB
 * @package    org.sabel.db.pdo
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
abstract class Sabel_DB_Pdo_Statement extends Sabel_DB_Abstract_Statement
{
  abstract public function escape(array $values);
  
  public function setDriver($driver)
  {
    if ($driver instanceof Sabel_DB_Pdo_Driver) {
      $this->driver = $driver;
    } else {
      $message = "driver should be an instance of Sabel_DB_Pdo_Driver";
      throw new Sabel_Exception_InvalidArgument($message);
    }
  }
  
  public function execute()
  {
    $query = $this->getQuery();
    
    if (empty($this->bindValues)) {
      $result = $this->driver->execute($query);
    } else {
      $bindValues = $this->escape($this->bindValues);
      foreach ($bindValues as $k => $v) {
        $bindValues[":{$k}"] = $v;
        unset($bindValues[$k]);
      }
      
      $query  = preg_replace('/@(.+?)@/', ':$1', $query);
      $result = $this->driver->execute($query, $bindValues);
    }
    
    if ($this->isInsert() && $this->seqColumn !== null) {
      return $this->driver->getLastInsertId();
    } elseif ($this->isUpdate() || $this->isDelete()) {
      return $this->driver->getAffectedRows();
    } else {
      return $result;
    }
  }
}
