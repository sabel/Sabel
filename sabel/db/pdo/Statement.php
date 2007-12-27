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
  protected $placeHolderPrefix = ":";
  protected $placeHolderSuffix = "";
  
  abstract public function escape(array $values);
  
  public function __construct(Sabel_DB_Pdo_Driver $driver)
  {
    $this->driver = $driver;
  }
  
  protected function isVarcharOfDefaultNull($column)
  {
    return ($column->isString() && $column->default === null);
  }
}
