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
 * @package    org.sabel.core
 * @author     Mori Reo <mori.reo@gmail.com>
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
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
    if (!class_exists($className, false)) {
      self::autoload($className);
    }
  }
  
  static function autoload($className)
  {
    if (isset(self::$required[$className])) return;

    $cache = self::getCache();
    if (!($path = $cache->read($className))) {
      $path = self::convertPath($className);
      $cache->write($className, $path);
    }
    
    if ($fullPath = self::isReadable($path)) {
      require ($fullPath);
      self::$required[$className] = 1;
    }
  }
  
  public static function fileUsing($path, $once = false)
  {
    if ($once && isset(self::$fileUsing[$path])) return true;
    
    $readable = false;
    $cache = self::getCache();
    
    if ($cache->read($path)) {
      $readable = true;
    } elseif (is_readable($path)) {
      $cache->write($path, true);
      $readable = true;
    }
    
    if ($readable) {
      ($once) ? require_once ($path) : require ($path);
      return self::$fileUsing[$path] = true;
    } else {
      return false;
    }
  }
  
  private static function convertPath($className)
  {
    static $suffix = null;
    
    if ($suffix === null) {
      $suffix = (defined("PHP_SUFFIX")) ? PHP_SUFFIX : ".php";
    }
    
    $prePath = str_replace("_", DS, $className);
    $path = strtolower(dirname($prePath)) . DS . basename($prePath) . $suffix;
    
    return str_replace("." . DS, "", $path);
  }
  
  private static function isReadable($path)
  {
    $cache = self::getCache();
    if ($fullPath = $cache->read($path)) {
      return $fullPath;
    } else {
      static $includePath = null;
      static $paths = null;
      
      if ($includePath === null) {
        $includePath = get_include_path();
      } elseif (($incPath = get_include_path()) !== $includePath) {
        $includePath = $incPath;
        $paths = null;
      }
      
      if ($paths === null) {
        $paths = explode(PATH_SEPARATOR, $includePath);
      }
      
      foreach ($paths as $p) {
        $fullPath = $p . DS . $path;
        if (is_readable($fullPath)) {
          $cache->write($path, $fullPath);
          return $fullPath;
        }
      }
      
      return false;
    }
  }
  
  private static function getCache()
  {
    if (self::$cache === null) {
      self::$cache = Sabel_Cache_Manager::getUsableCache();
    }
    
    return self::$cache;
  }
  
  public static function main()
  {
    define("DS", DIRECTORY_SEPARATOR);
    define("IS_WIN", (DS === '\\'));
    define("DIR_DIVIDER", DS);
    
    self::$path = dirname(__FILE__);
    
    $SABEL = "sabel" . DS;
    
    require ($SABEL . "Object.php");
    require ($SABEL . "Functions.php");
    require ($SABEL . "Bus.php");
    require ($SABEL . "Config.php");
    require ($SABEL . "Context.php");
    require ($SABEL . "Environment.php");
    require ($SABEL . "Router.php");
    require ($SABEL . "Destination.php");
    require ($SABEL . "Request.php");
    require ($SABEL . "Response.php");
    
    $BUS     = $SABEL . "bus"        . DS;
    $CACHE   = $SABEL . "cache"      . DS;
    $MAP     = $SABEL . "map"        . DS;
    $REQUEST = $SABEL . "request"    . DS;
    $STORAGE = $SABEL . "storage"    . DS;
    $VIEW    = $SABEL . "view"       . DS;
    $DB      = $SABEL . "db"         . DS;
    
    require ($BUS . "Config.php");
    require ($BUS . "Processor.php");
    
    require ($CACHE . "Manager.php");
    require ($CACHE . "Interface.php");
    require ($CACHE . "Apc.php");
    require ($CACHE . "Null.php");
    
    require ($MAP . "Candidate.php");
    require ($MAP . "Element.php");
    require ($MAP . "Config.php");
    require ($MAP . "Configurator.php");
    require ($MAP . "config" . DS . "Route.php");
    
    require ($REQUEST . "Object.php");
    require ($REQUEST . "Uri.php");
    require ($REQUEST . "AbstractBuilder.php");
    require ($REQUEST . "Builder.php");
    
    require ($STORAGE . "Interface.php");
    require ($STORAGE . "Abstract.php");
    require ($STORAGE . "Session.php");
    
    require ($VIEW . "Uri.php");
    require ($VIEW . "Renderer.php");
    require ($VIEW . "Repository.php");
    require ($VIEW . "Template.php");
    require ($VIEW . "template" . DS . "File.php");
    
    require ($DB . "Config.php");
    require ($DB . "Type.php");
    
    require ($SABEL . "router"     . DS . "Map.php");
    require ($SABEL . "controller" . DS . "Page.php");
    require ($SABEL . "response"   . DS . "Web.php");
    require ($SABEL . "exception"  . DS . "Runtime.php");
    require ($SABEL . "logger"     . DS . "File.php");
    require ($SABEL . "util"       . DS . "HashList.php");
    require ($SABEL . "addon"      . DS . "Loader.php");
  }
}

Sabel::main();
