<?php

/**
 * Sabel_DB_Pgsql_Migration
 *
 * @category   DB
 * @package    org.sabel.db
 * @subpackage pgsql
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_DB_Pgsql_Migration
{
  protected $search  = array('TYPE::INT(INCREMENT)',
                             'TYPE::BINT(INCREMENT)',
                             'TYPE::INT',
                             'TYPE::SINT',
                             'TYPE::BINT',
                             'TYPE::STRING',
                             'TYPE::TEXT',
                             'TYPE::DATETIME',
                             'TYPE::FLOAT',
                             'TYPE::DOUBLE',
                             'TYPE::BOOL',
                             '__TRUE__',
                             '__FALSE__');

  protected $replace = array('serial',
                             'bigserial',
                             'integer',
                             'smallint',
                             'bigint',
                             'varchar',
                             'text',
                             'timestamp',
                             'real',
                             'double precision',
                             'boolean',
                             'true',
                             'false');

  protected $model = null;

  public function setModel($tblName)
  {
    $mdlName     = convert_to_modelname($tblName);
    $this->model = @MODEL($mdlName);
  }

  public function addTable($tblName, $cmdQuery)
  {
    $cmdQuery = preg_replace("/[\n\r\f]/", '', $cmdQuery);

    $sch   = $this->search;
    $rep   = $this->replace;
    $query = str_replace($sch, $rep, $cmdQuery);
    $this->model->execute("CREATE TABLE $tblName ( " . $query . " )");
  }

  public function deleteTable($tblName)
  {
    $this->model->execute("DROP TABLE $tblName");
  }

  public function renameTable($from, $to)
  {
    $this->model->execute("ALTER TABLE $from RENAME TO $to");
  }

  public function addColumn($tblName, $colName, $param)
  {
    $sch  = $this->search;
    $rep  = $this->replace;
    $attr = str_replace($sch, $rep, $param);

    $this->model->execute("ALTER TABLE $tblName ADD $colName $attr");
  }

  public function deleteColumn($tblName, $colName)
  {
    $this->model->execute("ALTER TABLE $tblName DROP $colName");
  }

  public function changeColumn($tblName, $colName, $param)
  {
    $sch  = $this->search;
    $rep  = $this->replace;
    $attr = trim(str_replace($sch, $rep, $param));
    list ($type) = explode(' ', $attr);
    $this->model->execute("ALTER TABLE $tblName ALTER $colName TYPE $type");

    $attr = strtolower($attr);
    if (strpos($attr, 'not null') !== false) {
      $this->model->execute("ALTER TABLE $tblName ALTER $colName SET NOT NULL");
    } else {
      $this->model->execute("ALTER TABLE $tblName ALTER $colName DROP NOT NULL");
    }

    if (strpos($attr, 'default') !== false) {
      $default = str_replace('default', '', strstr($attr, 'default'));
      $this->model->execute("ALTER TABLE $tblName ALTER $colName SET DEFAULT $default");
    } else {
      $this->model->execute("ALTER TABLE $tblName ALTER $colName DROP DEFAULT");
    }
  }

  public function renameColumn($tblName, $from, $to)
  {
    $this->model->execute("ALTER TABLE $tblName RENAME $from TO $to");
  }
}
