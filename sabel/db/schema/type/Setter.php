<?php

/**
 * Sabel_DB_Schema_Type_Setter
 *
 * @category   DB
 * @package    org.sabel.db
 * @subpackage schema
 * @subpackage type
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_DB_Schema_Type_Setter
{
  public static function send($co, $type)
  {
    if ($type === 'date') {
      $co->type = Sabel_DB_Schema_Const::DATE;
    } else if ($type === 'time') {
      $co->type = Sabel_DB_Schema_Const::TIME;
    } else {
      $int    = new Sabel_DB_Schema_Type_Integer();
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
