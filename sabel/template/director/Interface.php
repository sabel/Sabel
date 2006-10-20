<?php

/**
 * Sabel_Template_Director_Interface
 *
 * @category   Template
 * @package    org.sabel.template.director
 * @author     Mori Reo <mori.reo@gmail.com>
 * @copyright  2002-2006 Mori Reo <mori.reo@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
interface Sabel_Template_Director_Interface
{
  public function decidePath();
  public function decideName();
}