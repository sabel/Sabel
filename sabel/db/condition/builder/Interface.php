<?php

/**
 * Sabel_DB_Condition_Builder_Interface
 *
 * @interface
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
interface Sabel_DB_Condition_Builder_Interface
{
  public function __construct(Sabel_DB_Abstract_Statement $stmt);

  public function build($condition);
  public function buildIsNull($key);
  public function buildIsNotNull($key);
  public function build(Sabel_DB_Condition_Object $condition);
  public function buildNormal(Sabel_DB_Condition_Object $condition);
  public function buildIn(Sabel_DB_Condition_Object $condition);
  public function buildLike(Sabel_DB_Condition_Object $condition);
  public function buildBetween(Sabel_DB_Condition_Object $condition);
  public function buildCompare(Sabel_DB_Condition_Object $condition);
}
