<?php

/**
 * Sabel_DB_Mysqli_Schema
 *
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_DB_Mysqli_Schema extends Sabel_DB_Mysql_Schema
{
  protected function getMysqlVersion()
  {
    $version = mysqli_get_server_version($this->driver->getConnection());
    $major = floor($version / 10000);
    $minor = floor(($version - $major * 10000) / 100);
    $sub   = $version - $major * 10000 - $minor * 100;
    
    return "{$major}.{$minor}.{$sub}";
  }
}
