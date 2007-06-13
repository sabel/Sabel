<?php

/**
 * Sabel_DB_Migration_Common
 *
 * @abstract
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
abstract class Sabel_DB_Migration_Common extends Sabel_DB_Migration_Base
{
  /*
  public function addColumn()
  {
    $cols = $this->createColumns();
    $tblName = convert_to_tablename($this->mdlName);

    if ($this->type === "upgrade") {
      foreach ($cols as $col) {
        $line = $this->createColumnAttributes($col);
        $this->executeQuery("ALTER TABLE $tblName ADD " . $line);
      }
    } else {
      foreach ($cols as $col) {
        $line = $this->createColumnAttributes($col);
        $this->executeQuery("ALTER TABLE $tblName DROP " . $col->name);
      }
    }
  }
  */

  /*
  public function dropColumn()
  {
    $tblName = convert_to_tablename($this->mdlName);

    if ($this->type === "upgrade") {
      $cols = $this->getDropColumns();
      $this->writeCurrentColumnsAttr($cols);

      foreach ($cols as $column) {
        $this->executeQuery("ALTER TABLE $tblName DROP $column");
      }
    } else {
      $cols = $this->createColumns($this->getRestoreFileName());
      foreach ($cols as $col) {
        $line = $this->createColumnAttributes($col);
        $this->executeQuery("ALTER TABLE $tblName ADD " . $line);
      }
    }
  }
  */
}
