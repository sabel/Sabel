<?php

class Sabel_DB_Schema_TypeSetter
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
      $tInt   = new Sabel_DB_Schema_TypeInt();
      $tStr   = new Sabel_DB_Schema_TypeStr();
      $tText  = new Sabel_DB_Schema_TypeText();
      $tTime  = new Sabel_DB_Schema_TypeTime();
      $tFloat = new Sabel_DB_Schema_TypeFloat();
      $tByte  = new Sabel_DB_Schema_TypeByte();
      $tOther = new Sabel_DB_Schema_TypeOther();

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
