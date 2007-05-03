<?php

/**
 * view plugin
 *
 * @category   Controller
 * @package    org.sabel.controller.plugin
 * @author     Mori Reo <mori.reo@gmail.com>
 * @copyright  2002-2006 Mori Reo <mori.reo@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Controller_Plugin_View extends Sabel_Controller_Page_Plugin
{
  public function render($template, $additional)
  {
    $this->rendered = Sabel_View::Render($template, $additional);
  }
}
