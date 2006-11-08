<?php

/**
 * Sabel_Template_Engine
 *
 * @abstract
 * @category   Template
 * @package    org.sabel.template
 * @author     Mori Reo <mori.reo@gmail.com>
 * @copyright  2002-2006 Mori Reo <mori.reo@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
abstract class Sabel_Template_Engine
{
  protected
    $tplpath = null,
    $tplname = null,
    $trim = true;
    
  protected static $attributes = array();
  
  public static function setAttribute($key, $value)
  {
    self::$attributes[$key] = $value;
  }
  
  public static function getAttirbute($key)
  {
    return self::$attributes[$key];
  }
  
  public static function getAttributes()
  {
    return self::$attributes;
  }
  
  public function assign($key, $value)
  {
    self::setAttribute($key, $value);
  }
  
  public function assignByArray($array)
  {
    if (is_array($array))
      array_merge(self::$attributes, $array);
  }
  
  public function setTemplateName($name)
  {
    $this->tplname = $name;
  }
  
  public function setTemplatePath($path)
  {
    $this->tplpath = $path;
  }
  
  protected function getTemplateFullPath()
  {
    return $this->tplpath . $this->tplname;
  }
  
  protected function getHelperPath()
  {
    
  }
  
  public function inter($uri)
  {
    $container = Container::create();
    $front     = $container->load('sabel.controller.Front');
    $response  = $front->ignition($uri);
    return $response['html'];
  }
  
  public function assgin($key, $value)
  {
    $this->attributes[$key] = $value;
  }
  
  public function partial($tplName)
  {
    return $this->load($tplName);
  }
  
  public function load($name)
  {
    $t = clone $this;
    $t->setTemplateName($name . '.tpl');
    return $t->retrieve();
  }
}
