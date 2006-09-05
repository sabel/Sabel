<?php

class String implements Iterator
{
  protected $string;
  protected $length   = 0;
  protected $position = 0;
  
  public function __construct($string = null)
  {
    $this->string = $string;
    $this->length = strlen($string);
  }
  
  public function __get($name)
  {
    return $this->string;
  }
  
  public static function create($string = null)
  {
    return new self($string);
  }
  
  public function isEmpty()
  {
    return empty($this->string);
  }
  
  public function isNotEmpty()
  {
    return (!$this->isEmpty());
  }
  
  public function isntEmpty()
  {
    return $this->isNotEmpty();
  }
  
  public function position($needle, $offset = 0)
  {
    return strpos($this->string, $needle, $offset);
  }
  
  public function rtrim($charlist = null)
  {
    if ($charlist) {
      return $this->string = rtrim($this->string, $charlist);
    } else {
      return $this->string = rtrim($this->string);
    }
  }
  
  public function ltrim($charlist = null)
  {
    if ($charlist) {
      return $this->string = ltrim($this->string, $charlist);
    } else {
      return $this->string = ltrim($this->string);
    }
  }
  
  public function trim($charlist = null)
  {
    if ($charlist) {
      return $this->string = trim($this->string, $charlist);
    } else {
      return $this->string = trim($this->string);
    }
  }
  
  public function toUpper()
  {
    return $this->string = strtoupper($this->string);
  }
  
  public function toLower()
  {
    return $this->string = strtolower($this->string);
  }
  
  public function toUpperFirst()
  {
    return $this->string = ucfirst($this->string);
  }
  
  public function explode($separator, $limit = null)
  {
    return explode($separator, $this->string);
  }
  
  public function sha1()
  {
    return sha1($this->string);
  }
  
  public function md5()
  {
    return md5($this->string);
  }
  
  public function replace($search, $replace)
  {
    return $this->string = str_replace($search, $replace, $this->string);
  }
  
  /**
   * @todo implement boundary value
   */
  public function succ()
  {
    $string = $this->string;

    for ($i = $this->length; $i > 0; $i--) {
      $p = $i-1;
      if ($string[$p] == 9) {
        $string[$p] = 0;
        $str        = 1;
      } else if ($string[$p] === 'z') {
        $string[$p] = 'a';
        $str        = 'a';
      } else if ($string[$p] === 'Z') {
        $string[$p] = 'A';
        $str        = 'A';
      } else if (preg_match('/[^a-zA-Z0-9]/', $string[$p])) {
        break;
      } else {
        $string[$p] = chr(ord($string[$p])+1);
        break;
      }
      if ($p === 0) {
        $string = $str . $string;
      } else if (preg_match('/[^a-zA-Z0-9]/', $string[$p-1])) {
        $string = substr($string, 0, $p) . $str . substr($string, $p);
        break;
      }
    }
    $this->length = strlen($string);
    return $this->string = $string;
  }
  
  public function last()
  {
    return $this->string{$this->length-1};
  }
  
  public function cloning()
  {
    return clone $this;
  }
  
  public function count()
  {
    return $this->length;
  }
  
  public function length()
  {
    return $this->length;
  }
  
  public function dump()
  {
    return var_dump($this->string);
  }
  
  public function export()
  {
    return var_export($this->string, 1);
  }
  
  /**
   * implements for Iterator interface
   *
   */
  public function current() {
    return self::create($this->string{$this->position});
  }
  
  /**
   * implements for Iterator interface
   *
   */
  public function key()
  {
    return $this->position;
  }
  
  /**
   * implements for Iterator interface
   *
   */
  public function next()
  {
    return $this->position++;
  }
  
  /**
   * implements for Iterator interface
   *
   */
  public function rewind()
  {
    $this->position = 0;
  }
  
  /**
   * implements for Iterator interface
   *
   */
  public function valid()
  {
    return ($this->position < $this->length);
  }
}

class ValueObject
{
  protected $values = array();
  
  public function __construct($values = null)
  {
    $this->values = (!is_null($values)) ? $values : null;
  }
  
  public static function create()
  {
    return new self();
  }
  
  public function __get($key)
  {
    return (isset($this->values[$key])) ? $this->values[$key] : null;
  }
  
  public function __set($key, $value)
  {
    $this->values[$key] = $value;
  }
}