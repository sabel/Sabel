<?php

/**
 * Sabel_DB_Migration_Reader
 *
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_DB_Migration_Reader
{
  private $filePath = "";

  public function __construct($filePath)
  {
    $this->filePath = $filePath;
  }

  public function readCreate()
  {
    $create = new Sabel_DB_Migration_Create();
    eval ($this->parse());
    return $create->build();
  }

  public function readAddColumn()
  {
    $add = new Sabel_DB_Migration_AddColumn();
    eval ($this->parse());
    return $add->build();
  }

  public function readDropColumn()
  {
    $drop = new Sabel_DB_Migration_DropColumn();
    eval ($this->parse());
    return $drop;
  }

  public function readChangeColumn()
  {
    $change = new Sabel_DB_Migration_ChangeColumn();
    eval ($this->parse());
    return $change;
  }

  public function readQuery()
  {
    $query = new Sabel_DB_Migration_Query();
    eval ($this->parse());
    return $query;
  }

  public function parse()
  {
    $content = file_get_contents($this->filePath);
    $content = str_replace("->default(", "->defaultValue(", $content);
    return str_replace(array("<?php", "?>"), "", $content);
  }
}
