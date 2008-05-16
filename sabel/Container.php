<?php

/**
 * Sabel Container
 *
 * @category   Container
 * @package    org.sabel.container
 * @author     Mori Reo <mori.reo@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Container
{
  private static $configs = array();
  
  public static function create($config)
  {
    if (!$config instanceof Sabel_Container_Injection) {
      $message = var_export($config, 1) . " is not Sabel_Container_Injection.";
      throw new Sabel_Exception_InvalidArgument($message);
    }
    
    $name = get_class($config);
    
    if (isset(self::$configs[$name])) {
      return self::$configs[$name];
    }
    
    $injector = new Sabel_Container_Injector($config);
    return self::$configs[$name] = $injector;
  }
  
  /**
   * create new instance with injection config
   */
  public static function load($className, $config)
  {
    return self::create($config)->newInstance($className);
  }
}
