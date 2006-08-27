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
    return (!(empty($this->string)));
  }
  
  public function isntEmpty()
  {
    return $this->isNotEmpty();
  }
  
  public function position($needle, $offset = 0)
  {
    return strpos($this->string, $needle);
  }
  
  public function rtrim($charlist = null)
  {
    if ($charlist) {
      return rtrim($this->string, $charlist);
    } else {
      return rtrim($this->string);
    }
  }
  
  public function ltrim($charlist = null)
  {
    if ($charlist) {
      return ltrim($this->string, $charlist);
    } else {
      return ltrim($this->string);
    }
  }
  
  public function trim($charlist = null)
  {
    if ($charlist) {
      return trim($this->string, $charlist);
    } else {
      return trim($this->string);
    }
  }
  
  public function toUpper()
  {
    return strtoupper($this->string);
  }
  
  public function toLower()
  {
    return strtolower($this->string);
  }
  
  public function toUpperFirst()
  {
    return ucfirst($this->string);
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
    return str_replace($search, $replace, $this->string);
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