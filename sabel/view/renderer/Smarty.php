<?php

/**
 * Sabel_Template_Engine_Smarty
 *
 * @category   Template
 * @package    org.sabel.template.engine
 * @author     Mori Reo <mori.reo@gmail.com>
 * @copyright  2002-2006 Mori Reo <mori.reo@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_View_Renderer_Smarty extends Sabel_View_Renderer
{
  private $smarty  = null;
  
  public function __construct()
  {
    $this->smarty = new Smarty();
  }
  
  public function rendering($path, $name, $values)
  {
    $this->smarty->template_dir = $this->tplpath;
    $this->smarty->compile_id   = $this->tplpath;
    return $this->smarty->fetch($this->tplname);
  }
  
  public function configuration()
  {
    $this->smarty->compile_dir = RUN_BASE . '/data/compiled';
    $this->smarty->load_filter('output','trimwhitespace');
  }
}