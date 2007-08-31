<?php

/**
 * Sabel_DB_Type_Setter
 *
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_DB_Type_Setter
{
  public static function send($co, $type)
  {
    $int    = new Sabel_DB_Type_Integer();
    $bint   = new Sabel_DB_Type_Bigint();
    $sint   = new Sabel_DB_Type_Smallint();
    $string = new Sabel_DB_Type_String();
    $text   = new Sabel_DB_Type_Text();
    $bool   = new Sabel_DB_Type_Boolean();
    $dtime  = new Sabel_DB_Type_Datetime();
    $date   = new Sabel_DB_Type_Date();
    $double = new Sabel_DB_Type_Double();
    $float  = new Sabel_DB_Type_Float();
    $byte   = new Sabel_DB_Type_Byte();
    $other  = new Sabel_DB_Type_Other();

    $int->add($bint);
    $bint->add($sint);
    $sint->add($string);
    $string->add($text);
    $text->add($bool);
    $bool->add($dtime);
    $dtime->add($date);
    $date->add($double);
    $double->add($float);
    $float->add($byte);
    $byte->add($other);

    $int->send($co, $type);
  }
}
