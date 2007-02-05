<?php

/**
 * Sabel_DB_Base_Migration
 *
 * @abstract
 * @category   DB
 * @package    org.sabel.db
 * @subpackage base
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
abstract class Sabel_DB_Base_Migration
{
  protected $model = null;

  public function setModel($tblName, $connectName)
  {
    $this->model = @MODEL(convert_to_modelname($tblName));
    $this->model->setConnectName($connectName);
  }

  protected function parseForForeignKey($line)
  {
    $line = str_replace('FKEY', 'FOREIGN KEY', $line);
    return preg_replace('/\) /', ') REFERENCES ', $line, 1);
  }
}
