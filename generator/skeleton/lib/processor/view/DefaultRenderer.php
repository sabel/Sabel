<?php

/**
 * Processor_View
 *
 * @category   Processor
 * @package    lib.processor
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Processor_View_DefaultRenderer extends Sabel_View_Renderer
{
  public function rendering($_tpl_contents, $_tpl_values, $_tpl_path = null)
  {
    if ($_tpl_path === null) {
      $hash = $this->createHash($_tpl_contents);
      $_tpl_path = COMPILE_DIR_PATH . DS . $hash;
      file_put_contents($_tpl_path, $_tpl_contents);
    }

    extract($_tpl_values, EXTR_OVERWRITE);
    ob_start();
    include ($_tpl_path);
    return ob_get_clean();
  }
}
