<?php

class Sabel_DB_Schema_Type_Setter
{
  public static function send($co, $type)
  {
    if ($type === 'boolean' || $type === 'bool') {
      $co->type = Sabel_DB_Const::BOOL;
    } else if ($type === 'date') {
      $co->type = Sabel_DB_Const::DATE;
    } else if ($type === 'time') {
      $co->type = Sabel_DB_Const::TIME;
    } else {
      $tInt   = new Sabel_DB_Schema_Type_Int();
      $tStr   = new Sabel_DB_Schema_Type_Str();
      $tText  = new Sabel_DB_Schema_Type_Text();
      $tTime  = new Sabel_DB_Schema_Type_Time();
      $tFloat = new Sabel_DB_Schema_Type_Float();
      $tByte  = new Sabel_DB_Schema_Type_Byte();
      $tOther = new Sabel_DB_Schema_Type_Other();

      $tInt->add($tStr);
      $tStr->add($tText);
      $tText->add($tTime);
      $tTime->add($tFloat);
      $tFloat->add($tByte);
      $tByte->add($tOther);

      $tInt->send($co, $type);
    }
  }
}
