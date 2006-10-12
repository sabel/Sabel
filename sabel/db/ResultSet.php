<?php

class Sabel_DB_ResultSet implements Iterator
{
  protected $data    = array();
  protected $pointer = 0;

  public function __construct($results = null)
  {
    if (!empty($results)) {
      foreach ($results as $result) $this->data[] = $result;
    }
  }

  public function isEmpty()
  {
    return (empty($this->data));
  }

  public function add($result)
  {
    $this->data[] = $result;
  }

  public function getFirstItem()
  {
    return $this->data[0];
  }

  public function fetch()
  {
    $data = $this->data[$this->pointer];
    $this->pointer++;
    return $data;
  }

  public function fetchAll()
  {
    return (empty($this->data)) ? false : $this->data;
  }

  public function current()
  {
    return $this->data[$this->pointer];
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
    return ($this->pointer < sizeof($this->data));
  }
}
