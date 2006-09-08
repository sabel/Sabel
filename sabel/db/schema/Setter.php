<?php

class Sabel_DB_Schema_TypeSetter
{
  public static function send($co, $type)
  {
    if ($type === 'boolean' || $type === 'bool') {
      $co->type = Sabel_DB_Schema_Type::BOOL;
    } else if ($type === 'date') {
      $co->type = Sabel_DB_Schema_Type::DATE;
    } else if ($type === 'time') {
      $co->type = Sabel_DB_Schema_Type::TIME;
    } else {
      $tInt   = new Sabel_DB_Schema_TypeInt();
      $tStr   = new Sabel_DB_Schema_TypeStr();
      $tText  = new Sabel_DB_Schema_TypeText();
      $tTime  = new Sabel_DB_Schema_TypeTime();
      $tByte  = new Sabel_DB_Schema_TypeByte();
      $tOther = new Sabel_DB_Schema_TypeOther();

      $tInt->add($tStr);
      $tStr->add($tText);
      $tText->add($tTime);
      $tTime->add($tByte);
      $tByte->add($tOther);

      $tInt->send($co, $type);
    }
  }
}
