<?php

/**
 * Sabel_Template_Director_Factory
 *
 * @category   Template
 * @package    org.sabel.template.director
 * @author     Mori Reo <mori.reo@gmail.com>
 * @copyright  2002-2006 Mori Reo <mori.reo@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Template_Director_Factory
{
  public static function create($entry)
  {
    $destination = $entry->getDestination();
    
    $classPath  = Sabel_Core_Const::MODULES_DIR . $destination->module;
    $classPath .= '/extensions/CustomTemplateDirector.php';
    
    $commonsPath  = Sabel_Core_Const::COMMONS_DIR;
    $commonsPath .= '/extensions/CustomTemplateDirector.php';
    
    if (is_file($classPath)) {
      require ($classPath);
      return new CustomTemplateDirector($destination);
    } elseif (is_file($commonsPath)) {
      require ($commonsPath);
      return new CustomTemplateDirector($destination);
    } else {
      return new Sabel_Template_Director_Default($destination);
    }
  }
}