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
    $current = ord($this->last());
    $next = ++$current;
    $this->string{$this->length-1} = chr($next);
    return $this->string;
  }
  
  public function last()
  {
    return $this->string{$this->length()-1};
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