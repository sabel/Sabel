<?php

/**
 * Default Preprocessor
 *
 * @category   View
 * @package    org.sabel.view
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_View_Preprocessor_Default implements Sabel_View_Preprocessor_Interface
{
  public function execute($contents)
  {
    return $contents;
  }
}
