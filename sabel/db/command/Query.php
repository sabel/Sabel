<?php

/**
 * Sabel_DB_Command_Query
 *
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_DB_Command_Query
{
  const COMMAND = "QUERY";

  public static function build($command, $query, $inputs)
  {
    $result = Sabel_DB_Command_Before::execute(self::COMMAND, $command);
    if ($result !== Sabel_DB_Command_Before::CONTINUOUS) return;

    $driver = $command->getDriver();
    if ($inputs) $query = vsprintf($sql, $driver->escape($param));
    $driver->setSql($query);

    Sabel_DB_Command_After::execute(self::COMMAND, $command);
  }
}
