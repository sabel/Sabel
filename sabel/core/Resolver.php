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
    $classNames = array_map('ucfirst', $parts);
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
    
    // @todo use iterator.
    $classpath = '';
    for ($i = 0; $i < count($parts); $i++) {
      $last = ($i === (count($parts) - 1));
      $classpath .= ($last) ? $parts[$i] : strtolower($parts[$i]) . '.';
    }
    
    return $classpath;
  }
}
