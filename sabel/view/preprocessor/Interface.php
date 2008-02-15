<?php

/**
 * Interface of Renderer Preprocessor
 *
 * @interface
 * @category   View
 * @package    org.sabel.view
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
interface Sabel_View_Preprocessor_Interface
{
  public function execute($contents);
}
