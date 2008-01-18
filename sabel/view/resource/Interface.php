<?php

/**
 * Sabel_View_Resource_Interface
 *
 * @interface
 * @category   View
 * @package    org.sabel.view
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
interface Sabel_View_Resource_Interface
{
  public function __construct($locationName, $path);
  public function getPath();
  public function fetch();
  public function isValid();
}
