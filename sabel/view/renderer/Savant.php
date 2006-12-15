<?php

/**
 * Sabel_View_Renderer_Savant
 *
 * @category   Template
 * @package    org.sabel.template.engine
 * @author     Mori Reo <mori.reo@gmail.com>
 * @copyright  2002-2006 Mori Reo <mori.reo@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_View_Renderer_Savant extends Sabel_View_Renderer
{
  private $savant  = null;
  
  public function __construct()
  {
    $this->savant = new Savant3();
  }
  
  public function enableCache()
  {
    $savant = $this->savant;
  }
  
  public function rendering($path, $name, $values)
  {
    $savant = $this->savant;
    
    foreach ($values as $k => $v) $savant->assign($k, $v);
    
    dump($path.$name);
    exit;
    return $savant->fetch($path.$name);
  }
}
