<?php

/**
 * Sabel_DB_Mssql_Migration
 *
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_DB_Mssql_Migration extends Sabel_DB_Abstract_Migration
{
  protected $types = array(Sabel_DB_Type::INT      => "INTEGER",
                           Sabel_DB_Type::BIGINT   => "BIGINT",
                           Sabel_DB_Type::SMALLINT => "SMALLINT",
                           Sabel_DB_Type::FLOAT    => "REAL",
                           Sabel_DB_Type::DOUBLE   => "DOUBLE PRECISION",
                           Sabel_DB_Type::BOOL     => "BIT",
                           Sabel_DB_Type::STRING   => "VARCHAR",
                           Sabel_DB_Type::TEXT     => "VARCHAR(MAX)",
                           Sabel_DB_Type::DATETIME => "DATETIME",
                           Sabel_DB_Type::DATE     => "DATETIME",
                           Sabel_DB_Type::BINARY   => "VARBINARY(MAX)");
}
