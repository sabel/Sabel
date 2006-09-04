<?php

class TypeSetter
{
  public function __construct($co, $type)
  {
    if ($type === 'boolean' || $type === 'bool') {
      $co->type = Sabel_DB_Schema_Type::BOOL;
    } else if ($type === 'date') {
      $co->type = Sabel_DB_Schema_Type::DATE;
    } else if ($type === 'time') {
      $co->type = Sabel_DB_Schema_Type::TIME;
    } else {
      $tInt   = new TypeInt();
      $tStr   = new TypeStr();
      $tText  = new TypeText();
      $tTime  = new TypeTime();
      $tByte  = new TypeByte();
      $tOther = new TypeOther();

      $tInt->add($tStr);
      $tStr->add($tText);
      $tText->add($tTime);
      $tTime->add($tByte);
      $tByte->add($tOther);

      $tInt->send($co, $type);
    }
  }
}