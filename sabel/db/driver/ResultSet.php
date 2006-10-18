<?php

class Sabel_DB_Driver_ResultSet implements Iterator
{
  const ASSOC  = 0;
  const NUM    = 5;
  const OBJECT = 10;
  const SCHEMA = 15;

  protected $assocRow = array();
  protected $arrayRow = array();
  protected $pointer  = 0;

  public function __construct($results = null)
  {
    if (!empty($results)) {
      foreach ($results as $result) {
        $this->assocRow[] = $result;
        $this->arrayRow[] = array_values($result);
      }
    }
  }

  public function isEmpty()
  {
    return (empty($this->assocRow));
  }

  public function add($result)
  {
    if (is_array($result)) {
      $this->assocRow[] = $result;
      $this->arrayRow[] = array_values($result);
    }
  }

  public function getFirstItem()
  {
    return $this->assocRow[0];
  }

  public function fetch($style = self::ASSOC)
  {
    if ($this->valid()) {
      $data = ($style === self::ASSOC) ? $this->assocRow[$this->pointer]
                                       : $this->arrayRow[$this->pointer];

      $this->pointer++;
      return $data;
    }
  }

  public function fetchAll($style = self::ASSOC)
  {
    $data = ($style === self::ASSOC) ? $this->assocRow : $this->arrayRow;
    return (empty($data)) ? false : $data;
  }

  public function current()
  {
    return $this->assocRow[$this->pointer];
  }

  public function key()
  {
    return $this->pointer;
  }

  public function next()
  {
    $this->pointer++;
  }

  public function rewind()
  {
    $this->pointer = 0;
  }

  public function valid()
  {
    return ($this->pointer < sizeof($this->assocRow));
  }
}
