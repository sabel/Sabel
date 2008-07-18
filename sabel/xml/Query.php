<?php

/**
 * Sabel_Xml_Query
 *
 * @category   XML
 * @package    org.sabel.sakle
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Xml_Query
{
  const WHITE_SPACE = "__@@SBLWS@@__";
  
  public static function toXpath($query)
  {
    list ($query, $hash) = self::convertWhiteSpaceInValue($query);
    
    $parts = explode(" ", $query);
    $xpath = "";
    
    $i = 0;
    while (true) {
      if (!isset($parts[$i])) break;
      
      $lowered = strtolower($parts[$i]);
      if ($lowered === "or" || $lowered === "and") {
        $xpath .= " {$lowered} ";
        $i++;
      } elseif ($lowered === "not") {
        $i = $i + 4;
      } else {
        $xpath .= self::createPartOfXpath($parts[$i], $parts[$i + 1], $parts[$i + 2], $hash);
        $i = $i + 3;
      }
    }
    
    return $xpath;
  }
  
  protected static function createPartOfXpath($path, $exp, $value, $hash)
  {
    $value = str_replace(array("__{$hash}__", self::WHITE_SPACE), array("", " "), $value);
    $path  = str_replace(".", "/", $path);
    
    if (strpos($path, "@") === false) {
      $path .= "/text()";
    } else {
      $path = str_replace("@", "/@", $path);
    }
    
    if ($exp === "=" || $exp === "!=") {
      return "{$path}{$exp}{$value}";
    } elseif (strtolower($exp) === "like") {
      $_value = substr($value, 1, -1);
      $first  = $_value{0};
      $last   = $_value{strlen($_value) - 1};
      
      if ($first === "%" && $last === "%") {
        return "contains({$path}, '" . substr($_value, 1, -1) . "')";
      } elseif ($first === "%" && $last !== "%") {
        return "ends-with({$path}, '" . substr($_value, 1) . "')";
      } elseif ($first !== "%" && $last === "%") {
        return "starts-with({$path}, '" . substr($_value, 0, -1) . "')";
      } else {
        return "contains({$path}, '{$_value}')";
      }
    }
  }
  
  protected static function convertWhiteSpaceInValue($query)
  {
    $random = md5hash();
    $length = strlen($query);
    $ret    = "";
    $prev   = null;
    $inVal  = false;
    
    for ($i = 0; $i < $length; $i++) {
      $char = $query{$i};
      
      if ($char === "'" && $prev !== "\\") {
        if (!$inVal) {
          $inVal = true;
          $ret  .= "__{$random}__@'";
          $prev  = "'";
        } else {
          $inVal = false;
          $ret  .= "'@__";
          $prev  = "'";
        }
      } else {
        $ret .= $char;
        $prev = $char;
      }
    }
    
    preg_match_all("~__{$random}__@'(.+)'@__~U", $ret, $matches);
    foreach ($matches[1] as $i => $value) {
      $replace = str_replace(" ", self::WHITE_SPACE, $value);
      $ret = str_replace("__{$random}__@'{$value}'@__", "__{$random}__'{$replace}'__{$random}__", $ret);
    }
    
    $ret = preg_replace("/ {2,}/", " ", $ret);
    return array($ret, $random);
  }
}
