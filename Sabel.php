<?php

/**
 * Sabel - Rapid Web Application Development Framework
 *
 * Copyright (c) 2005-2008 Mori Reo <mori.reo@sabel.jp>
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
 * @author     Mori Reo <mori.reo@sabel.jp>
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2005-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
final class Sabel
{
  private static $readableFiles = array();
  private static $required  = array();
  private static $fileUsing = array();
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
  
  public static function autoload($className)
  {
    if (isset(self::$required[$className])) return;
    
    if (isset(self::$readableFiles[$className])) {
      require (self::$readableFiles[$className]);
      self::$required[$className] = 1;
    } elseif ($path = self::getFilePath($className)) {
      require ($path);
      self::$required[$className] = 1;
      self::$readableFiles[$className] = $path;
    }
  }
  
  public static function fileUsing($path, $once = false)
  {
    if ($once && isset(self::$fileUsing[$path])) return true;
    
    if (isset(self::$readableFiles[$path])) {
      $readable = true;
    } elseif (is_readable($path)) {
      $readable = true;
      self::$readableFiles[$path] = $path;
    } else {
      $readable = false;
    }
    
    if ($readable) {
      ($once) ? require_once ($path) : require ($path);
      self::$fileUsing[$path] = 1;
      return true;
    }
    
    return false;
  }
  
  private static function getFilePath($className)
  {
    static $suffix = null;
    static $includePath = null;
    static $paths = null;
    
    if ($suffix === null) {
      $suffix = (defined("PHP_SUFFIX")) ? PHP_SUFFIX : ".php";
    }
    
    $exp = explode("_", $className);
    
    if (count($exp) === 1) {
      $path = $exp[0] . $suffix;
    } else {
      $class = array_pop($exp);
      $prePath = implode("/", array_map("strtolower", $exp));
      $path = $prePath . DIRECTORY_SEPARATOR . $class . $suffix;
    }
    
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
      $fullPath = $p . DIRECTORY_SEPARATOR . $path;
      if (is_readable($fullPath)) return $fullPath;
    }
    
    return false;
  }
  
  public static function main()
  {
    self::$path = dirname(__FILE__);
    $SABEL = "sabel" . DIRECTORY_SEPARATOR;
    
    require ($SABEL . "Object.php");
    require ($SABEL . "Functions.php");
    require ($SABEL . "Environment.php");
    require ($SABEL . "Bus.php");
    require ($SABEL . "Config.php");
    require ($SABEL . "Context.php");
    require ($SABEL . "Request.php");
    require ($SABEL . "Response.php");
    
    $BUS     = $SABEL . "bus"        . DIRECTORY_SEPARATOR;
    $CACHE   = $SABEL . "cache"      . DIRECTORY_SEPARATOR;
    $MAP     = $SABEL . "map"        . DIRECTORY_SEPARATOR;
    $REQUEST = $SABEL . "request"    . DIRECTORY_SEPARATOR;
    $CTRL    = $SABEL . "controller" . DIRECTORY_SEPARATOR;
    $SESSION = $SABEL . "session"    . DIRECTORY_SEPARATOR;
    $VIEW    = $SABEL . "view"       . DIRECTORY_SEPARATOR;
    
    require ($BUS  . "Config.php");
    require ($BUS  . "Processor.php");
    
    require ($CACHE . "Interface.php");
    require ($CACHE . "File.php");
    
    require ($MAP . "Configurator.php");
    require ($MAP . "Candidate.php");
    require ($MAP . "Elements.php");
    require ($MAP . "Element.php");
    require ($MAP . "Destination.php");
    require ($MAP . "config" . DIRECTORY_SEPARATOR . "Route.php");
    
    require ($REQUEST . "Object.php");
    require ($REQUEST . "Uri.php");
    require ($REQUEST . "AbstractBuilder.php");
    require ($REQUEST . "Builder.php");
    
    require ($CTRL . "Page.php");
    require ($CTRL . "Redirector.php");
    
    require ($SESSION . "Abstract.php");
    require ($SESSION . "PHP.php");
    
    require ($VIEW . "Renderer.php");
    require ($VIEW . "Repository.php");
    require ($VIEW . "Template.php");
    require ($VIEW . "template" . DIRECTORY_SEPARATOR . "File.php");
    
    require ($SABEL . "response"   . DIRECTORY_SEPARATOR . "Object.php");
    require ($SABEL . "exception"  . DIRECTORY_SEPARATOR . "Runtime.php");
    require ($SABEL . "logger"     . DIRECTORY_SEPARATOR . "File.php");
    require ($SABEL . "util"       . DIRECTORY_SEPARATOR . "HashList.php");
  }
  
  public static function init()
  {
    if (ENVIRONMENT === PRODUCTION) {
      self::$readableFiles = Sabel_Cache_File::create()->read("readable_files");
    }
  }
  
  public static function shutdown()
  {
    if (ENVIRONMENT === PRODUCTION) {
      Sabel_Cache_File::create()->write("readable_files", self::$readableFiles);
    }
  }
}

Sabel::main();
