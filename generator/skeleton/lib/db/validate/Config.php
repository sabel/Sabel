<?php

/**
 * Custom Validation
 *
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Db_Validate_Config extends Sabel_DB_Validate_Config
{
  public function configure()
  {
    // $this->model("MODEL_NAME")
    //      ->column("COLUMN_NAME")
    //      ->validator("VALIDATION_METHOD1");
    //
    // $this->model("MODEL_NAME")
    //      ->column("COLUMN_NAME")
    //      ->validator("VALIDATION_METHOD2", "ARG1", "ARG2");
    //
    // ...
  }
  
  public function VALIDATION_METHOD1($model, $colName, $localzedName)
  {
    // if (EXPRESSION === IS_NOT_VALID) {
    //    return "ERROR_MESSAGE";
    // }
  }
  
  public function VALIDATION_METHOD2($model, $colName, $localzedName, $arg1, $arg2)
  {
    // if (EXPRESSION === IS_NOT_VALID) {
    //    return "ERROR_MESSAGE";
    // }
  }
}
