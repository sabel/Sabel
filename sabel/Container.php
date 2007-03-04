<?php

/**
 * Sabel Container
 *
 * @category   container
 * @package    org.sabel.core
 * @author     Mori Reo <mori.reo@gmail.com>
 * @copyright  2002-2006 Mori Reo <mori.reo@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Container
{
  public static function load($className, $configClass = "Dependency_Config")
  {
    $di = new Sabel_Container_DI();
    
    $self = new self();
    $conf = $self->loadConfig($className, $configClass);
    
    $confMethod = str_replace("_", "", $className);
    if (in_array($confMethod, get_class_methods($conf))) {
      $config = $conf->$confMethod();
    }
    
    if (isset($config->aspect)) {
      if (isset($config->aspect->use) && $config->aspect->use) {
        return new Sabel_Aspect_Proxy($di->load($className));
      }
    }
    
    return $di->load($className);    
  }
  
  public function loadConfig($className, $configClass)
  {
    return new $configClass();
  }
}