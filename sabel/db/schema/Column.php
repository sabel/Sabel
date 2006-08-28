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
        $data['maxValue'] =  127;
        $data['minValue'] = -128;
        break;
      case 'int2':
      case 'smallint':
        $data['maxValue'] =  32767;
        $data['minValue'] = -32768;
        break;
      case 'mediumint':
        $data['maxValue'] =  8388607;
        $data['minValue'] = -8388608;
        break;
      case 'int':
      case 'int4':
      case 'integer':
        $data['maxValue'] =  2147483647;
        $data['minValue'] = -2147483648;
        break;
      case 'int8':
      case 'bigint':
        $data['maxValue'] =  9223372036854775807;
        $data['minValue'] = -9223372036854775808;
        break;
    }
  }
}
