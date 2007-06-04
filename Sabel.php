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

if (!defined("DIR_DIVIDER")) define("DIR_DIVIDER", "/");
if (defined("DIR_DIVIDER")) define("D", DIR_DIVIDER);
define("DEFAULT_PHP_SUFFIX", ".php");
define("CURRENT_PATH", dirname(__FILE__));
set_include_path(CURRENT_PATH . ":" . get_include_path());

// regist autoload static method
spl_autoload_register(array("Sabel", "autoload"));

define("SBL", CURRENT_PATH . DIR_DIVIDER . "sabel" . DIR_DIVIDER);

require (SBL . "Functions.php");
require (SBL . "cache" . DIR_DIVIDER . "Manager.php");
require (SBL . "cache" . DIR_DIVIDER . "Apc.php");
require (SBL . "cache" . DIR_DIVIDER . "Null.php");

require (SBL . "Const.php");
require (SBL . "Context.php");
require (SBL . "Object.php");

require (SBL . "Map.php");
require (SBL . "map" . DIR_DIVIDER . "Candidate.php");
require (SBL . "map" . DIR_DIVIDER . "Config.php");
require (SBL . "map" . DIR_DIVIDER . "Configurator.php");
require (SBL . "map" . DIR_DIVIDER . "Selecter.php");
require (SBL . "map" . DIR_DIVIDER . "Tokens.php");
require (SBL . "map" . DIR_DIVIDER . "selecter" . DIR_DIVIDER . "Impl.php");

require (SBL . "Request.php");
require (SBL . "request" . DIR_DIVIDER . "Web.php");
require (SBL . "request" . DIR_DIVIDER . "Uri.php");
require (SBL . "request" . DIR_DIVIDER . "Parameters.php");

require (SBL . "Plugin.php");
require (SBL . "plugin" . DIR_DIVIDER . "Base.php");
require (SBL . "plugin" . DIR_DIVIDER . "Common.php");

require (SBL . "Response.php");
require (SBL . "response" . DIR_DIVIDER . "Abstract.php");
require (SBL . "response" . DIR_DIVIDER . "Web.php");

require (SBL . "Container.php");
require (SBL . "container" . DIR_DIVIDER . "Injector.php");
require (SBL . "container" . DIR_DIVIDER . "Bind.php");
require (SBL . "container" . DIR_DIVIDER . "DI.php");
require (SBL . "container" . DIR_DIVIDER . "ReflectionClass.php");
require (SBL . "container" . DIR_DIVIDER . "Injection.php");

require (SBL . "controller" . DIR_DIVIDER . "Front.php");
require (SBL . "controller" . DIR_DIVIDER . "Executer.php");
require (SBL . "controller" . DIR_DIVIDER . "executer" . DIR_DIVIDER . "Basic.php");

require (SBL . "logger" . DIR_DIVIDER . "Factory.php");
require (SBL . "logger" . DIR_DIVIDER . "Interface.php");
require (SBL . "logger" . DIR_DIVIDER . "File.php");
require (SBL . "logger" . DIR_DIVIDER . "Null.php");

require (SBL . "db" . DIR_DIVIDER . "Config.php");

require (SBL . "Destination.php");

require (SBL . "View.php");
require (SBL . "view" . DIR_DIVIDER . "Renderer.php");
require (SBL . "view" . DIR_DIVIDER . "renderer" . DIR_DIVIDER . "Class.php");
require (SBL . "view" . DIR_DIVIDER . "Resource.php");
require (SBL . "view" . DIR_DIVIDER . "resource" . DIR_DIVIDER . "File.php");
require (SBL . "view" . DIR_DIVIDER . "resource" . DIR_DIVIDER . "Template.php");
require (SBL . "view" . DIR_DIVIDER . "Locator.php");
require (SBL . "view" . DIR_DIVIDER . "locator" . DIR_DIVIDER . "Factory.php");
require (SBL . "view" . DIR_DIVIDER . "locator" . DIR_DIVIDER . "File.php");

require (SBL . "storage" . DIR_DIVIDER . "Session.php");
require (SBL . "Helper.php");

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
  private static $cache = null;
  
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
        require (CURRENT_PATH . DIR_DIVIDER . $path);
      } else {
        require ($p . DIR_DIVIDER . $path);
      }
      
      self::$required[$className] = 1;
    }
  }
  
  public static function fileUsing($path, $once = false)
  {
    if (!isset(self::$fileUsing[$path])) {
      if (!is_readable($path)) {
        throw new Exception("{$path} file not found");
      }
      
      if ($once) {
        require_once ($path);
      } else {
        require ($path);
      }
      
      self::$fileUsing[$path] = true;
    }
  }
  
  private static function convertPath($className)
  {
    $prePath = str_replace("_", DIR_DIVIDER, $className);
    $path = strtolower(dirname($prePath)) . DIR_DIVIDER 
            . basename($prePath) . DEFAULT_PHP_SUFFIX;
    
    return str_replace(".".DIR_DIVIDER, "", $path);
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
        $fpath = $p . DIR_DIVIDER . $path;
        if (is_readable($fpath)) {
          self::$cache->write($path, $p);
          return $p;
        }
      }
      
      return false;
    }
  }
}
