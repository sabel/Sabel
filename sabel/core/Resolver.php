<?php

/**
 * Sabel_Core_Resolver
 * 
 * @package org.sabel
 * @author Mori Reo <mori.reo@gmail.com>
 */
class Sabel_Core_Resolver
{
  public static function resolvClassName($classpath)
  {
    $parts = explode('.', $classpath);
    foreach ($parts as $pos => $name) {
      $classNames[] = ucfirst($name);
    }
    
    return implode('_', $classNames);
  }
  
  public static function resolvPath($classpath)
  {
    return str_replace('.', '/', $classpath);
  }
  
  public static function resolvClassPathByClassName($name)
  {
    $parts = explode('_', $name);
    
    if (count($parts) === 1) return $name;
    
    $classPath = '';
    for ($i = 0; $i < count($parts); $i++) {
      $last = ($i === (count($parts) - 1));
      $classPath .= ($last) ? $parts[$i] : strtolower($parts[$i]) . '.';
    }
    
    return $classPath;
  }
}

?>