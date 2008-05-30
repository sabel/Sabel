<?php

/**
 * Custom Validation
 *
 * @category   DB
 * @package    lib.db
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Db_Validate_Config extends Sabel_DB_Validate_Config
{
  public function configure()
  {
    // $this->model("MODEL_NAME")
    //      ->column("COLUMN_NAME")
    //      ->validator("VALIDATE_METHOD1");
    //
    // $this->model("MODEL_NAME")
    //      ->column("COLUMN_NAME")
    //      ->validator("VALIDATE_METHOD2", "ARG1", "ARG2");
    //
    // ...
  }
  
  public function VALIDATE_METHOD1($model, $colName, $localizedName)
  {
    // if (!EXPRESSION) {
    //    return "ERROR_MESSAGE";
    // }
  }
  
  public function VALIDATE_METHOD2($model, $colName, $localizedName, $arg1, $arg2)
  {
    // if (!EXPRESSION) {
    //    return "ERROR_MESSAGE";
    // }
  }
}
