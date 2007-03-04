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
  public static function load($className)
  {
    $refClass = new ReflectionClass($className);
    if ($refClass->isAbstract() || $refClass->isInterface()) {
      $self = new self();
      $conf = $self->loadConfig($className);
      
      $confMethod = str_replace("_", "", $className);
      $config = $conf->$confMethod();
      $impl = $config->implementation;
      
      if (isset($config->aspect)) {
        if (isset($config->aspect->use) && $config->aspect->use) {
          return new Sabel_Aspect_Proxy(new $impl());
        }
      }
      
      return new $impl();
    } else {
      return new $className();
    }
  }
  
  public function loadConfig($className)
  {
    return new Dependency_Config();
  }
}