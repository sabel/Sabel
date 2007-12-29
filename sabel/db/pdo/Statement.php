<?php

/**
 * Sabel_DB_Pdo_Statement
 *
 * @abstract
 * @category   DB
 * @package    org.sabel.db.pdo
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
abstract class Sabel_DB_Pdo_Statement extends Sabel_DB_Abstract_Statement
{
  abstract public function escape(array $values);
  
  public function __construct(Sabel_DB_Pdo_Driver $driver)
  {
    $this->driver = $driver;
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
    }
    
    return $result;
  }
  
  protected function isVarcharOfDefaultNull($column)
  {
    return ($column->isString() && $column->default === null);
  }
}
