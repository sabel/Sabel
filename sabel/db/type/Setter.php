<?php

/**
 * Sabel_DB_Type_Setter
 *
 * @category   DB
 * @package    org.sabel.db
 * @subpackage type
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_DB_Type_Setter
{
  public static function send($co, $type)
  {
    if ($type === 'date') {
      $co->type = Sabel_DB_Type_Const::DATE;
    } elseif ($type === 'time') {
      $co->type = Sabel_DB_Type_Const::TIME;
    } else {
      $int    = Sabel::load('Sabel_DB_Type_Integer');
      $string = Sabel::load('Sabel_DB_Type_String');
      $text   = Sabel::load('Sabel_DB_Type_Text');
      $time   = Sabel::load('Sabel_DB_Type_Datetime');
      $double = Sabel::load('Sabel_DB_Type_Double');
      $float  = Sabel::load('Sabel_DB_Type_Float');
      $byte   = Sabel::load('Sabel_DB_Type_Byte');
      $other  = Sabel::load('Sabel_DB_Type_Other');

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
