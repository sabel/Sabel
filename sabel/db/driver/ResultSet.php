<?php

class Sabel_DB_Driver_ResultSet implements Iterator
{
  const ASSOC  = 0;
  const NUM    = 5;
  const OBJECT = 10;

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

  public function fetch($style = self::ASSOC)
  {
    if (!$this->valid()) return null;

    switch ($style) {
      case self::ASSOC:
        $data = $this->assocRow[$this->pointer];
        break;
      case self::NUM:
        $data = $this->arrayRow[$this->pointer];
        break;
      case self::OBJECT:
        $data = new Sabel_DB_Driver_ResultObject($this->assocRow[$this->pointer]);
        break;
    }

    $this->pointer++;
    return (empty($data)) ? false : $data;
  }

  public function fetchAll($style = self::ASSOC)
  {
    switch ($style) {
      case self::ASSOC:
        $data = $this->assocRow;
        break;
      case self::NUM:
        $data = $this->arrayRow;
        break;
      case self::OBJECT:
        $data = array();
        foreach ($this->assocRow as $row) {
          $data[] = new Sabel_DB_Driver_ResultObject($row);
        }
        break;
    }

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
