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
  public function initialize($driver);
  public function build($condition);

  public function buildIsNull($key);
  public function buildIsNotNull($key);

  public function buildIn($condition);
  public function buildNormal($condition);
  public function buildBetween($condition);
  public function buildLike($condition);
  public function buildCompare($condition);
}
