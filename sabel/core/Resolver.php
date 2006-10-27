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
 
  /**
   * return e.g. sabel.core.Example from Sabel_Core_Example
   *
   * @param string $name class name (Sabel_Core_Example)
   * @return string classpath (sabel.core.Example)
   */
  public static function resolvClassPathByClassName($name)
  {
    $parts = explode('_', $name);
    
    if (count($parts) === 1) return $name;
    
    $className = array_pop($parts);
    $parts = array_map('strtolower', $parts);
    $parts[] = $className;
    return implode('.', $parts);
  }
}