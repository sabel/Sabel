<?php

/**
 * Sabel - rapid web application development framework
 *
 * Copyright (c) 2006 Mori Reo <mori.reo@gmail.com>
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *
 *     * Redistributions of source code must retain the above copyright
 *       notice, this list of conditions and the following disclaimer.
 *     * Redistributions in binary form must reproduce the above copyright
 *       notice, this list of conditions and the following disclaimer in the
 *       documentation and/or other materials provided with the distribution.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE
 * ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE
 * LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR
 * CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF
 * SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS
 * INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN
 * CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
 * ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 */

/* regist autoload static method */
spl_autoload_register(array("Sabel", "autoload"));

/**
 * Sabel
 *
 * @category   Sabel
 * @package    org.sabel
 * @author     Mori Reo <mori.reo@gmail.com>
 * @copyright  2002-2006 Mori Reo <mori.reo@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
final class Sabel
{
  private static $required  = array();
  private static $fileUsing = array();
  private static $cache     = null;
  private static $path      = "";
  
  public static function getPath()
  {
    return self::$path;
  }
  
  public static function using($className)
  {
    if (!class_exists($className, false)) self::autoload($className);
  }
  
  static function autoload($className)
  {
    if (self::$cache === null) {
      self::$cache = Sabel_Cache_Manager::create();
    }
    
    if (isset(self::$required[$className])) return;

    if (!($path = self::$cache->read($className))) {
      $path = self::convertPath($className);
      self::$cache->write($className, $path);
    }
    
    if (($p = self::isReadable($path)) !== false) {
      if ($p === true) {
        require (self::$path . DS . $path);
      } else {
        require ($p . DS . $path);
      }
      
      self::$required[$className] = 1;
    }
  }
  
  public static function fileUsing($path, $once = false)
  {
    if ($once && isset(self::$fileUsing[$path])) return true;
    
    if (is_readable($path)) {
      ($once) ? require_once ($path) : require ($path);
      return self::$fileUsing[$path] = true;
    } else {
      return false;
    }
  }
  
  private static function convertPath($className)
  {
    $prePath = str_replace("_", DS, $className);
    $path = strtolower(dirname($prePath)) . DS . basename($prePath) . PHP_SUFFIX;
    
    return str_replace("." . DS, "", $path);
  }
  
  private static function isReadable($path)
  {
    if ($p = self::$cache->read($path)) {
      return $p;
    } else {
      if (is_readable($path)) return true;
      
      static $paths = null;

      if ($paths === null) {
        $includePath = get_include_path();
        $paths = explode(":", $includePath);
      }
      
      foreach ($paths as $p) {
        $fpath = $p . DS . $path;
        if (is_readable($fpath)) {
          self::$cache->write($path, $p);
          return $p;
        }
      }
      
      return false;
    }
  }
  
  public static function main()
  {
    self::$path = dirname(__FILE__);
    
    define("IS_WIN", (DIRECTORY_SEPARATOR === '\\'));
    define("DIR_DIVIDER", DIRECTORY_SEPARATOR);
    define("DS", DIRECTORY_SEPARATOR);
    
    define("PHP_SUFFIX", ".php");
    define("TPL_SUFFIX", ".tpl");
    
    $SABEL = "sabel" . DS;
    
    require ($SABEL . "Functions.php");
    require ($SABEL . "Bus.php");
    require ($SABEL . "Config.php");
    require ($SABEL . "Context.php");
    require ($SABEL . "Object.php");
    require ($SABEL . "Router.php");
    require ($SABEL . "Request.php");
    require ($SABEL . "Destination.php");
    require ($SABEL . "Container.php");
    require ($SABEL . "Response.php");
    require ($SABEL . "Helper.php");
    require ($SABEL . "View.php");
    
    $BUS        = $SABEL . "bus"        . DS;
    $CACHE      = $SABEL . "cache"      . DS;
    $MAP        = $SABEL . "map"        . DS;
    $REQUEST    = $SABEL . "request"    . DS;
    $RESPONSE   = $SABEL . "response"   . DS;
    $VIEW       = $SABEL . "view"       . DS;
    $CONTAINER  = $SABEL . "container"  . DS;
    $CONTROLLER = $SABEL . "controller" . DS;
    $ANNOTATION = $SABEL . "annotation" . DS;
    $LOG        = $SABEL . "logger"     . DS;
    
    require ($BUS . "Config.php");
    require ($BUS . "Processor.php");
    require ($BUS . "ProcessorCallback.php");
    
    require ($CACHE . "Manager.php");
    require ($CACHE . "Apc.php");
    require ($CACHE . "Null.php");
    
    require ($MAP . "Candidate.php");
    require ($MAP . "Config.php");
    require ($MAP . "Configurator.php");
    require ($MAP . "config" . DS . "Route.php");
    
    require ($REQUEST . "Object.php");
    require ($REQUEST . "Uri.php");
    require ($REQUEST . "Parameters.php");
    require ($REQUEST . "AbstractBuilder.php");
    require ($REQUEST . "Builder.php");
    
    require ($ANNOTATION . "Reader.php");
    require ($ANNOTATION . "ReflectionClass.php");
    require ($ANNOTATION . "ReflectionMethod.php");
    
    require ($CONTROLLER . "Creator.php");
    require ($CONTROLLER . "Page.php");
    
    require ($RESPONSE . "Abstract.php");
    require ($RESPONSE . "Web.php");
    
    require ($CONTAINER . "Injector.php");
    require ($CONTAINER . "Bind.php");
    require ($CONTAINER . "DI.php");
    require ($CONTAINER . "ReflectionClass.php");
    require ($CONTAINER . "Injection.php");
    
    require ($VIEW . "Uri.php");
    require ($VIEW . "Resource.php");
    require ($VIEW . "Renderer.php");
    require ($VIEW . "Locator.php");
    require ($VIEW . "renderer" . DS . "Class.php");
    require ($VIEW . "resource" . DS . "File.php");
    require ($VIEW . "resource" . DS . "Template.php");
    require ($VIEW . "locator"  . DS . "Factory.php");
    require ($VIEW . "locator"  . DS . "File.php");
    
    require ($LOG . "Factory.php");
    require ($LOG . "Interface.php");
    require ($LOG . "File.php");
    require ($LOG . "Null.php");
    
    require ($SABEL . "storage"   . DS . "Session.php");
    require ($SABEL . "router"    . DS . "Map.php");
    require ($SABEL . "exception" . DS . "Runtime.php");
  }
}

Sabel::main();
