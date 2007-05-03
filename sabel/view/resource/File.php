<?php

/**
 * Sabel_View_Resource_File
 *
 * @category   View
 * @package    org.sabel.view
 * @author     Mori Reo <mori.reo@gmail.com>
 * @copyright  2002-2006 Mori Reo <mori.reo@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
interface Sabel_View_Resource_File extends Sabel_View_Resource
{
  public function setPath($path);
  public function setName($name);
  public function setFullPath($path, $name);
}
