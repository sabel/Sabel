<?php

/**
 * Manipulator
 *
 * @category   DB
 * @package    lib.db
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Manipulator extends Sabel_DB_Manipulator
{
  public function before($method)
  {
    /* example.
    $method = "before" . ucfirst($method);
    if (method_exists($this, $method)) {
      return $this->$method();
    }
    */
  }
  
  public function after($method, $result)
  {
    if (ENVIRONMENT === DEVELOPMENT) {
      $this->log();
    }
  }
  
  private function log()
  {
    /* simple logging. */
    
    static $selectLog = null;
    static $insertLog = null;
    static $updateLog = null;
    static $deleteLog = null;
    static $queryLog  = null;
    
    $stmt = $this->stmt;
    if (is_object($stmt)) {
      if ($stmt->isSelect()) {
        $name = "select";
      } elseif ($stmt->isInsert()) {
        $name = "insert";
      } elseif ($stmt->isUpdate()) {
        $name = "update";
      } elseif ($stmt->isDelete()) {
        $name = "delete";
      } else {
        $name = "query";
      }
      
      $logger = $name . "Log";
      if ($$logger === null) {
        $$logger = new Sabel_Logger_File($name . ".log");
      }
      
      $sql = $stmt->getSql();
      if ($bindParams = $stmt->getBindParams()) {
        $bindParams = $stmt->getDriver()->escape($bindParams);
        $sql = str_replace(array_keys($bindParams), $bindParams, $sql);
      }
      
      $$logger->log($sql);
    }
  }
}
