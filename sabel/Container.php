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
  static $instancies = array();
  
  public static function load($className, $additional = null)
  {
    if ($additional === null) $additional = array();
    
    if (isset($additional["singleton"]) && $additional["singleton"] && isset(self::$instancies[$className])) {
      return self::$instancies[$className];
    }
    
    $di = new Sabel_Container_DI();
    
    $self = new self();
    if (isset($additional["config"])) {
      $conf = $self->loadConfig($className, $additional["config"]);
    } else {
      $conf = $self->loadConfig($className, "Dependency_Config");
    }
    
    if ($conf) {
      $confMethod = str_replace("_", "", $className);
      if (in_array($confMethod, get_class_methods($conf))) {
        $config = $conf->$confMethod();
      }
    } else {
      $config = new StdClass();
    }
    
    if (isset($config->implementation)) {
      Sabel_Context::log("load from container " . $className . " implement class " . $config->implementation);
    } else {
      Sabel_Context::log("load from container " . $className);
    }
    
    if (isset($config->aspect)) {
      if (isset($config->aspect->use) && $config->aspect->use) {
        if (isset($config->aspect->aspects)) {
          foreach ($config->aspect->aspects as $aspect) {
            $pc = Sabel_Aspect_Pointcut::create($aspect);
            foreach ($config->aspect->methods as $method) {
              $pc->addMethod($method);
            }
            Sabel_Context::log("apply $aspect aspect for class $className on " . join(", ", $config->aspect->methods));
            Sabel_Aspect_Aspects::singleton()->addPointcut($pc);
          }
        }
        
        $solved_instance = $di->load($className);
        $instance = new Sabel_Aspect_Proxy($solved_instance);
      } else {
        $instance = $di->load($className);
      }
    } else {
      $instance = $di->load($className);
    }
    
    if (isset($additional["singleton"]) && $additional["singleton"] === true) {
      if (isset(self::$instancies[$className])) {
        return self::$instancies[$className];
      } else {
        self::$instancies[$className] = $instance;
        return $instance;
      }
    } else {
      return $instance;
    }
  }
  
  public function loadConfig($className, $configClass)
  {
    if (class_exists($configClass)) {
      return new $configClass();
    } else {
      return false;
    }
  }
}