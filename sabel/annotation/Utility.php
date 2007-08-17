<?php

/**
 * Sabel_Annotation_Utility
 *
 * @category   Annotation
 * @package    org.sabel.annotation
 * @author     Mori Reo <mori.reo@gmail.com>
 * @copyright  2002-2006 Mori Reo <mori.reo@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Annotation_Utility
{
  public static function processAnnotation($line)
  {
    $annotation = preg_split("/ +/", self::removeComment($line));
    
    if (strpos($annotation[0], "@") === 0) {
      $name       = array_shift($annotation);
      $annotation = (count($annotation) > 2) ? $annotation : $annotation[0];
      
      return new Sabel_Annotation_Context(ltrim($name, "@ "), $annotation);
    }
  }
  
  protected static function removeComment($line)
  {
    $line = preg_replace('/^\/?[\s\*]+/', "", trim($line));
    return  preg_replace('/[\s\*]+\/$/',  "", $line);
  }
}
