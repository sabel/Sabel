<?php

/**
 * Sabel_Util_String
 *
 * @category   Util
 * @package    org.sabel.util
 * @author     Mori Reo <mori.reo@gmail.com>
 *             Hamanaka Kazuhiro <hamanaka.kazuhiro@gmail.com>
 *             Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Mori Reo <mori.reo@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Util_String extends Sabel_Object
{
  protected
    $string = "",
    $length = 0;
  
  public function __construct($string = "")
  {
    if (is_string($string)) {
      $this->string = $string;
      $this->length = strlen($string);
    } else {
      throw new Sabel_Exception_InvalidArgument("argument must be a string.");
    }
  }
  
  public function __toString()
  {
    return $this->string;
  }
  
  public function length()
  {
    return $this->length;
  }
  
  public function isEmpty()
  {
    return ($this->string === "");
  }
  
  public function equals($value)
  {
    $args = func_get_args();
    if (empty($args)) return false;
    
    foreach ($args as $string) {
      if ($string instanceof self) {
        $string = $string->toString();
      }
      
      if ($this->string === $string) {
        return true;
      }
    }
    
    return false;
  }
  
  public function append($string)
  {
    $this->string .= $string;
    $this->length  = strlen($this->string);
    
    return $this;
  }
  
  public function charAt($pos)
  {
    if ($pos >= $this->length || $pos < 0) {
      return "";
    } else {
      return $this->string{$pos};
    }
  }
  
  public function last()
  {
    $pos = $this->length - 1;
    return $this->string{$pos};
  }
  
  public function indexOf($needle, $offset = 0)
  {
    return strpos($this->string, $needle, $offset);
  }
  
  public function trim($charlist = null)
  {
    $this->doTrim("trim", $charlist);
    
    return $this;
  }
  
  public function rtrim($charlist = null)
  {
    $this->doTrim("rtrim", $charlist);
    
    return $this;
  }
  
  public function ltrim($charlist = null)
  {
    $this->doTrim("ltrim", $charlist);
    
    return $this;
  }
  
  private function doTrim($func, $charlist)
  {
    if ($charlist === null) {
      $this->string = $func($this->string);
    } else {
      $this->string = $func($this->string, $charlist);
    }
    
    $this->length = strlen($this->string);
  }
  
  public function toUpperCase()
  {
    $this->string = strtoupper($this->string);
    
    return $this;
  }
  
  public function toLowerCase()
  {
    $this->string = strtolower($this->string);
    
    return $this;
  }
  
  public function ucfirst()
  {
    $this->string = ucfirst($this->string);
    
    return $this;
  }
  
  public function lcfirst()
  {
    if ($this->isEmpty()) return "";
    
    $this->string = lcfirst($this->string);
    return $this;
  }
  
  public function insert($offset, $string)
  {
    $tmp  = $this->substring(0, $offset);
    $tmp .= $string . $this->substring($offset);
    
    $this->string = $tmp;
    $this->length = strlen($tmp);
    
    return $this;
  }
  
  public function replace($search, $replace)
  {
    $this->string = str_replace($search, $replace, $this->string);
    
    return $this;
  }
  
  public function substring($start, $length = null)
  {
    if ($length === null) {
      $string = substr($this->string, $start);
    } else {
      $string = substr($this->string, $start, $length);
    }
    
    return new self($string);
  }
  
  public function split($separator, $limit = null)
  {
    return new Sabel_Util_Map(explode($separator, $this->string));
  }
  
  public function sha1()
  {
    return sha1($this->string);
  }
  
  public function md5()
  {
    return md5($this->string);
  }
  
  public function cloning()
  {
    return clone $this;
  }
  
  /**
   * @todo implement boundary value
   */
  public function succ()
  {
    $string = $this->string;
    
    for ($i = $this->length; $i > 0; $i--) {
      $p = $i - 1;
      if ($string[$p] == 9) {
        $string[$p] = 0;
        $str        = 1;
      } elseif ($string[$p] === "z") {
        $string[$p] = "a";
        $str        = "a";
      } elseif ($string[$p] === "Z") {
        $string[$p] = "A";
        $str        = "A";
      } elseif (preg_match("/[^a-zA-Z0-9]/", $string[$p])) {
        break;
      } else {
        $string[$p] = chr(ord($string[$p]) + 1);
        break;
      }
      if ($p === 0) {
        $string = $str . $string;
      } elseif (preg_match("/[^a-zA-Z0-9]/", $string[$p - 1])) {
        $string = substr($string, 0, $p) . $str . substr($string, $p);
        break;
      }
    }
    
    $this->length = strlen($string);
    $this->string = $string;
    
    return $this;
  }
}
