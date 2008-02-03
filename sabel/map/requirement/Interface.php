<?php

/**
 * Interface for requirement
 *
 * @interface
 * @category   Map
 * @package    org.sabel.map.requirement
 * @author     Mori Reo <mori.reo@sabel.jp>
 * @copyright  2002-2006 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
interface Sabel_Map_Requirement_Interface
{
  public function isMatch($token);
}
