<?php

class Sabel_DB_Schema_Type_Setter
{
  public static function send($co, $type)
  {
    if ($type === 'date') {
      $co->type = Sabel_DB_Const::DATE;
    } else if ($type === 'time') {
      $co->type = Sabel_DB_Const::TIME;
    } else {
      $int    = new Sabel_DB_Schema_Type_Int();
      $string = new Sabel_DB_Schema_Type_String();
      $text   = new Sabel_DB_Schema_Type_Text();
      $time   = new Sabel_DB_Schema_Type_Time();
      $double = new Sabel_DB_Schema_Type_Double();
      $float  = new Sabel_DB_Schema_Type_Float();
      $byte   = new Sabel_DB_Schema_Type_Byte();
      $other  = new Sabel_DB_Schema_Type_Other();

      $int->add($string);
      $string->add($text);
      $text->add($time);
      $time->add($double);
      $double->add($float);
      $float->add($byte);
      $byte->add($other);

      $int->send($co, $type);
    }
  }
}
