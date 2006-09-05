<?php

class Sabel_Annotation_Utility
{
  public static function processAnnotation($line)
  {
    $annotation = preg_split('/ +/', self::removeComment($line));
    
    if (strpos($annotation[0], '@') === 0) {
      $name       = array_shift($annotation);
      $annotation = (count($annotation) > 2) ? $annotation : $annotation[0];
      
      return new Sabel_Annotation_Context(ltrim($name, '@ '), $annotation);
    }
  }
  
  protected static function removeComment($line)
  {
    $line =     preg_replace('/^\*/',     '', trim($line));
    $line =     preg_replace('/\*\/$/',   '',      $line);
    return trim(preg_replace('/^\/\*\*/', '',      $line));
  }
}
  