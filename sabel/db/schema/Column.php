<?php

class Sabel_DB_Schema_Column
{
  protected $data = array();

  public function __set($key, $val)
  {
    $this->data[$key] = $val;
  }

  public function __get($key)
  {
    return $this->data[$key];
  }

  public function setNumericRange($type)
  {
    $data =& $this->data;

    switch($type) {
      case 'tinyint':
        $data['max'] =  127;
        $data['min'] = -128;
        break;
      case 'int2':
      case 'smallint':
        $data['max'] =  32767;
        $data['min'] = -32768;
        break;
      case 'mediumint':
        $data['max'] =  8388607;
        $data['min'] = -8388608;
        break;
      case 'int':
      case 'int4':
      case 'integer':
        $data['max'] =  2147483647;
        $data['min'] = -2147483648;
        break;
      case 'int8':
      case 'bigint':
        $data['max'] =  9223372036854775807;
        $data['min'] = -9223372036854775808;
        break;
    }
  }
}
