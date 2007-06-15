<?php

/**
 * Sabel_DB_Command_Base
 *
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
abstract class Sabel_DB_Command_Base
{
  protected abstract function run($command);

  public function execute($executer)
  {
    $result = Sabel_DB_Command_Before::execute($this->command, $executer);
    if ($result !== Sabel_DB_Command_Before::CONTINUOUS) return;

    $result = $this->run($executer);

    Sabel_DB_Command_After::execute($this->command, $executer, $result);
  }
}
