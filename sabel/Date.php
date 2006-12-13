<?php

/**
 * Sabel_Date
 *
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Date
{
  const NORMAL  = 0;
  const ATOM    = 1;
  const RSS     = 2;
  const COOKIE  = 3;
  const ISO     = 4;
  const RFC822  = 5;
  const RFC850  = 6;
  const RFC1036 = 7;
  const RFC1123 = 8;
  const RFC2822 = 9;
  const RFC     = 10;
  const W3C     = 11;
  const JP      = 12;

  protected $formats = array(self::NORMAL  => array(
                             'full' => 'Y-m-d H:i:s',
                             'date' => 'Y-m-d',
                             'time' => 'H:i:s'),

                           self::ATOM    => array(
                             'full' => 'c',
                             'date' => 'Y-m-d',
                             'time' => 'H:i:sP'),

                           self::RSS     => array(
                             'full' => 'r',
                             'date' => 'D, d M Y',
                             'time' => 'H:i:s O'),

                           self::COOKIE  => array(
                             'full' => 'l, d-M-y H:i:s T',
                             'date' => 'l, d-M-y',
                             'time' => 'H:i:s T'),

                           self::ISO     => array(
                             'full' => 'Y-m-d\TH:i:sO',
                             'date' => 'Y-m-d',
                             'time' => 'H:i:sO'),

                           self::RFC822  => array(
                             'full' => 'D, d M y H:i:s O',
                             'date' => 'D, d M y',
                             'time' => 'H:i:s O'),

                           self::RFC850  => array(
                             'full' => 'l, d-M-y H:i:s T',
                             'date' => 'l, d-M-y',
                             'time' => 'H:i:s T'),

                           self::RFC1036 => array(
                             'full' => 'D, d M y H:i:s O',
                             'date' => 'D, d M y',
                             'time' => 'H:i:s O'),

                           self::RFC1123 => array(
                             'full' => 'r',
                             'date' => 'D, d M Y',
                             'time' => 'H:i:s O'),

                           self::RFC2822 => array(
                             'full' => 'r',
                             'date' => 'D, d M Y',
                             'time' => 'H:i:s O'),

                           self::RFC     => array(
                             'full' => 'r',
                             'date' => 'D, d M Y',
                             'time' => 'H:i:s O'),

                           self::W3C     => array(
                             'full' => 'c',
                             'date' => 'Y-m-d',
                             'time' => 'H:i:sP'),
                             
                           self::JP      => array(
                             "full" => "Y年m月d日 H時i分s秒",
                             "date" => "Y年m月d日",
                             "time" => "H時i分s秒"));

  protected $format = self::NORMAL;
  protected $data = array();
  protected $timestamp = null;

  public function __construct($arg = null)
  {
    if (isset($format)) $this->format= $format;

    if ($arg === null) {
      $this->timestamp = time();
    } elseif (is_string($arg)) {
      $this->timestamp = strtotime($arg);
    } elseif (is_array($arg)) {
      $y = (isset($arg['y'])) ? $arg['y'] : date('Y');
      $m = (isset($arg['m'])) ? $arg['m'] : date('m');
      $d = (isset($arg['d'])) ? $arg['d'] : date('d');
      $h = (isset($arg['h'])) ? $arg['h'] : 0;
      $i = (isset($arg['i'])) ? $arg['i'] : 0;
      $s = (isset($arg['s'])) ? $arg['s'] : 0;

      $this->timestamp = mktime($h, $i, $s, $m, $d, $y);
    } else {
      throw new Sabel_Exception_Runtime('Sabel_Date::__construct() invalid parameter.');
    }

    $this->reset();
  }

  protected function reset()
  {
    $this->data = array('year'    => $this->y(),
                        's_year'  => $this->y(true),
                        'month'   => $this->m(),
                        's_month' => $this->m(true),
                        'day'     => $this->d(),
                        's_day'   => $this->d(true),
                        'hour'    => $this->h(),
                        's_hour'  => $this->h(true),
                        'minute'  => $this->i(),
                        'second'  => $this->s());
  }

  public function __toString()
  {
    return $this->getDateTime();
  }

  public function __get($key)
  {
    return (isset($this->data[$key])) ? $this->data[$key] : null;
  }

  public function setFormat($format)
  {
    $this->format = $format;
    return $this;
  }

  public function getDateTime()
  {
    $format = $this->format;
    return date($this->formats[$format]['full'], $this->timestamp);
  }

  public function getDate()
  {
    $format = $this->format;
    return date($this->formats[$format]['date'], $this->timestamp);
  }

  public function getTime()
  {
    $format = $this->format;
    return date($this->formats[$format]['time'], $this->timestamp);
  }

  public function getYear($short = false)
  {
    if ($short) {
      return $this->getShortYear();
    } else {
      return date('Y', $this->timestamp);
    }
  }

  public function getShortYear()
  {
    return date('y', $this->timestamp);
  }

  public function getMonth($short = false)
  {
    if ($short) {
      return $this->getShortMonth();
    } else {
      return date('m', $this->timestamp);
    }
  }

  public function getShortMonth()
  {
    return date('n', $this->timestamp);
  }

  public function getStrMonth($short = false)
  {
    if ($short) {
      return $this->getShortStrMonth();
    } else {
      return date('F', $this->timestamp);
    }
  }

  public function getShortStrMonth()
  {
    return date('M', $this->timestamp);
  }

  public function getDay($short = false)
  {
    if ($short) {
      return $this->getShortDay();
    } else {
      return date('d', $this->timestamp);
    }
  }

  public function getShortDay()
  {
    return date('j', $this->timestamp);
  }

  public function getLastDay($short = false)
  {
    $timestamp = mktime($this->h(), $this->i(), $this->s(), $this->m() + 1, 0, $this->y());

    $format = ($short) ? 'j' : 'd';
    return date($format, $timestamp);
  }

  public function getHour($short = false)
  {
    if ($short) {
      return $this->getShortHour();
    } else {
      return date('H', $this->timestamp);
    }
  }

  public function getShortHour()
  {
    return date('G', $this->timestamp);
  }

  public function getHalfHour($short)
  {
    if ($short) {
      return $this->getShortHalfHour();
    } else {
      return date('h', $this->timestamp);
    }
  }

  public function getShortHalfHour()
  {
    return date('g', $this->timestamp);
  }

  public function getMinute()
  {
    return date('i', $this->timestamp);
  }

  public function getSecond()
  {
    return date('s', $this->timestamp);
  }

  public function getMeridiem($upper = false)
  {
    $format = ($upper) ? 'A' : 'a';
    return date($format, $this->timestamp);
  }

  public function getWeek($short = false)
  {
    if ($short) {
      return $this->getShortWeek();
    } else {
      return date('l', $this->timestamp);
    }
  }

  public function getShortWeek()
  {
    return date('D', $this->timestamp);
  }

  public function getNumericWeek()
  {
    return date('w', $this->timestamp);
  }

  public function ymd($sep = '-')
  {
    return $this->y() . $sep . $this->m() . $sep . $this->d();
  }

  public function his($sep = ':')
  {
    return $this->h() . $sep . $this->i() . $sep . $this->s();
  }

  public function y($short = false)
  {
    return $this->getYear($short);
  }

  public function m($short = false)
  {
    return $this->getMonth($short);
  }

  public function d($short = false)
  {
    return $this->getDay($short);
  }

  public function h($short = false)
  {
    return $this->getHour($short);
  }

  public function i()
  {
    return $this->getMinute();
  }

  public function s()
  {
    return $this->getSecond();
  }

  public function incYear($year = 1)
  {
    $year = $this->y() + $year;
    $this->timestamp = mktime($this->h(), $this->i(), $this->s(), $this->m(), $this->d(), $year);
    $this->reset();

    return $this->y();
  }

  public function incMonth($month = 1)
  {
    $month = $this->m() + $month;
    $this->timestamp = mktime($this->h(), $this->i(), $this->s(), $month, $this->d(), $this->y());
    $this->reset();

    return $this->m();
  }

  public function incDay($day = 1)
  {
    $this->timestamp += 86400 * $day;
    $this->reset();

    return $this->d();
  }

  public function incHour($hour = 1)
  {
    $this->timestamp += 3600 * $hour;
    $this->reset();

    return $this->h();
  }

  public function incMinute($min = 1)
  {
    $this->timestamp += 60 * $min;
    $this->reset();

    return $this->i();
  }

  public function incSecond($second = 1)
  {
    $this->timestamp += $second;
    $this->reset();

    return $this->s();
  }

  public function decYear($year = 1)
  {
    $year = $this->y() - $year;
    $this->timestamp = mktime($this->h(), $this->i(), $this->s(), $this->m(), $this->d(), $year);
    $this->reset();

    return $this->y();
  }

  public function decMonth($month = 1)
  {
    $month = $this->m() - $month;
    $this->timestamp = mktime($this->h(), $this->i(), $this->s(), $month, $this->d(), $this->y());
    $this->reset();

    return $this->m();
  }

  public function decDay($day = 1)
  {
    $this->timestamp -= 86400 * $day;
    $this->reset();

    return $this->d();
  }

  public function decHour($hour = 1)
  {
    $this->timestamp -= 3600 * $hour;
    $this->reset();

    return $this->h();
  }

  public function decMinute($min = 1)
  {
    $this->timestamp -= 60 * $min;
    $this->reset();

    return $this->m();
  }

  public function decSecond($second = 1)
  {
    $this->timestamp -= $second;
    $this->reset();

    return $this->s();
  }
}
