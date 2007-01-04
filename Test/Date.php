<?php

class Test_Date extends SabelTestCase
{
  protected $timezone = 'JST';

  public static function suite()
  {
    return self::createSuite("Test_Date");
  }

  public function __construct()
  {

  }

  public function setUp()
  {

  }

  public function tearDown()
  {

  }

  public function testNormal()
  {
    $time = '2005-08-15 10:00:00';
    $sd   = new Sabel_Date($time);
    $this->assertEquals($sd->getDateTime(), '2005-08-15 10:00:00');
    $this->assertEquals($sd->getDate(), '2005-08-15');
    $this->assertEquals($sd->getTime(), '10:00:00');

    $sd->incDay();
    $this->assertEquals($sd->getDateTime(), '2005-08-16 10:00:00');

    $sd->incDay(2);
    $this->assertEquals($sd->getDateTime(), '2005-08-18 10:00:00');
  }

  public function testAtom()
  {
    $time = '2005-08-15T10:00:00+09:00';
    $sd   = new Sabel_Date($time);
    $sd->setFormat(Sabel_Date::ATOM);
    $this->assertEquals($sd->getDateTime(), '2005-08-15T10:00:00+09:00');
    $this->assertEquals($sd->getDate(), '2005-08-15');
    $this->assertEquals($sd->getTime(), '10:00:00+09:00');

    $sd->incDay();
    $this->assertEquals($sd->getDateTime(), '2005-08-16T10:00:00+09:00');

    $sd->incDay(2);
    $this->assertEquals($sd->getDateTime(), '2005-08-18T10:00:00+09:00');
  }

  public function testCookie()
  {
    $time = 'Monday, 15-Aug-05 10:00:00 ' . $this->timezone;
    $sd   = new Sabel_Date($time);
    $sd->setFormat(Sabel_Date::COOKIE);
    $this->assertEquals($sd->getDateTime(), 'Monday, 15-Aug-05 10:00:00 ' . $this->timezone);
    $this->assertEquals($sd->getDate(), 'Monday, 15-Aug-05');
    $this->assertEquals($sd->getTime(), '10:00:00 ' . $this->timezone);

    $sd->incDay();
    $this->assertEquals($sd->getDateTime(), 'Tuesday, 16-Aug-05 10:00:00 ' . $this->timezone);

    $sd->incDay(2);
    $this->assertEquals($sd->getDateTime(), 'Thursday, 18-Aug-05 10:00:00 ' . $this->timezone);
  }

  public function testISO()
  {
    $time = '2005-08-15T10:00:00+0900';
    $sd   = new Sabel_Date($time);
    $sd->setFormat(Sabel_Date::ISO);
    $this->assertEquals($sd->getDateTime(), '2005-08-15T10:00:00+0900');
    $this->assertEquals($sd->getDate(), '2005-08-15');
    $this->assertEquals($sd->getTime(), '10:00:00+0900');

    $sd->incDay();
    $this->assertEquals($sd->getDateTime(), '2005-08-16T10:00:00+0900');

    $sd->incDay(2);
    $this->assertEquals($sd->getDateTime(), '2005-08-18T10:00:00+0900');
  }

  public function testRFC822()
  {
    $time = 'Mon, 15 Aug 05 10:00:00 +0900';
    $sd   = new Sabel_Date($time);
    $sd->setFormat(Sabel_Date::RFC822);
    $this->assertEquals($sd->getDateTime(), 'Mon, 15 Aug 05 10:00:00 +0900');
    $this->assertEquals($sd->getDate(), 'Mon, 15 Aug 05');
    $this->assertEquals($sd->getTime(), '10:00:00 +0900');

    $sd->incDay();
    $this->assertEquals($sd->getDateTime(), 'Tue, 16 Aug 05 10:00:00 +0900');

    $sd->incDay(2);
    $this->assertEquals($sd->getDateTime(), 'Thu, 18 Aug 05 10:00:00 +0900');
  }

  public function testRFC850()
  {
    $time = 'Monday, 15-Aug-05 10:00:00 ' . $this->timezone;
    $sd   = new Sabel_Date($time);
    $sd->setFormat(Sabel_Date::RFC850);
    $this->assertEquals($sd->getDateTime(), 'Monday, 15-Aug-05 10:00:00 ' . $this->timezone);
    $this->assertEquals($sd->getDate(), 'Monday, 15-Aug-05');
    $this->assertEquals($sd->getTime(), '10:00:00 ' . $this->timezone);

    $sd->incDay();
    $this->assertEquals($sd->getDateTime(), 'Tuesday, 16-Aug-05 10:00:00 ' . $this->timezone);

    $sd->incDay(2);
    $this->assertEquals($sd->getDateTime(), 'Thursday, 18-Aug-05 10:00:00 ' . $this->timezone);
  }

  public function testRFC1036()
  {
    $time = 'Mon, 15 Aug 05 10:00:00 +0900';
    $sd   = new Sabel_Date($time);
    $sd->setFormat(Sabel_Date::RFC1036);
    $this->assertEquals($sd->getDateTime(), 'Mon, 15 Aug 05 10:00:00 +0900');
    $this->assertEquals($sd->getDate(), 'Mon, 15 Aug 05');
    $this->assertEquals($sd->getTime(), '10:00:00 +0900');

    $sd->incDay();
    $this->assertEquals($sd->getDateTime(), 'Tue, 16 Aug 05 10:00:00 +0900');

    $sd->incDay(2);
    $this->assertEquals($sd->getDateTime(), 'Thu, 18 Aug 05 10:00:00 +0900');
  }

  public function testRFC1123()
  {
    $time = 'Mon, 15 Aug 2005 10:00:00 +0900';
    $sd   = new Sabel_Date($time);
    $sd->setFormat(Sabel_Date::RFC1123);
    $this->assertEquals($sd->getDateTime(), 'Mon, 15 Aug 2005 10:00:00 +0900');
    $this->assertEquals($sd->getDate(), 'Mon, 15 Aug 2005');
    $this->assertEquals($sd->getTime(), '10:00:00 +0900');

    $sd->incDay();
    $this->assertEquals($sd->getDateTime(), 'Tue, 16 Aug 2005 10:00:00 +0900');

    $sd->incDay(2);
    $this->assertEquals($sd->getDateTime(), 'Thu, 18 Aug 2005 10:00:00 +0900');
  }

  public function testRFC2822()
  {
    $time = 'Mon, 15 Aug 2005 10:00:00 +0900';
    $sd   = new Sabel_Date($time);
    $sd->setFormat(Sabel_Date::RFC2822);
    $this->assertEquals($sd->getDateTime(), 'Mon, 15 Aug 2005 10:00:00 +0900');
    $this->assertEquals($sd->getDate(), 'Mon, 15 Aug 2005');
    $this->assertEquals($sd->getTime(), '10:00:00 +0900');

    $sd->incDay();
    $this->assertEquals($sd->getDateTime(), 'Tue, 16 Aug 2005 10:00:00 +0900');

    $sd->incDay(2);
    $this->assertEquals($sd->getDateTime(), 'Thu, 18 Aug 2005 10:00:00 +0900');
  }

  public function testRFC()
  {
    $time = 'Mon, 15 Aug 2005 10:00:00 +0900';
    $sd   = new Sabel_Date($time);
    $sd->setFormat(Sabel_Date::RFC);
    $this->assertEquals($sd->getDateTime(), 'Mon, 15 Aug 2005 10:00:00 +0900');
    $this->assertEquals($sd->getDate(), 'Mon, 15 Aug 2005');
    $this->assertEquals($sd->getTime(), '10:00:00 +0900');

    $sd->incDay();
    $this->assertEquals($sd->getDateTime(), 'Tue, 16 Aug 2005 10:00:00 +0900');

    $sd->incDay(2);
    $this->assertEquals($sd->getDateTime(), 'Thu, 18 Aug 2005 10:00:00 +0900');
  }

  public function testRSS()
  {
    $time = 'Mon, 15 Aug 2005 10:00:00 +0900';
    $sd   = new Sabel_Date($time);
    $sd->setFormat(Sabel_Date::RSS);
    $this->assertEquals($sd->getDateTime(), 'Mon, 15 Aug 2005 10:00:00 +0900');
    $this->assertEquals($sd->getDate(), 'Mon, 15 Aug 2005');
    $this->assertEquals($sd->getTime(), '10:00:00 +0900');

    $sd->incDay();
    $this->assertEquals($sd->getDateTime(), 'Tue, 16 Aug 2005 10:00:00 +0900');

    $sd->incDay(2);
    $this->assertEquals($sd->getDateTime(), 'Thu, 18 Aug 2005 10:00:00 +0900');
  }

  public function testW3C()
  {
    $time = '2005-08-15T10:00:00+09:00';
    $sd   = new Sabel_Date($time);
    $sd->setFormat(Sabel_Date::W3C);
    $this->assertEquals($sd->getDateTime(), '2005-08-15T10:00:00+09:00');
    $this->assertEquals($sd->getDate(), '2005-08-15');
    $this->assertEquals($sd->getTime(), '10:00:00+09:00');

    $sd->incDay();
    $this->assertEquals($sd->getDateTime(), '2005-08-16T10:00:00+09:00');

    $sd->incDay(2);
    $this->assertEquals($sd->getDateTime(), '2005-08-18T10:00:00+09:00');
  }

  public function testJP()
  {
    $time = '2005-08-15 10:00:27';
//    $time = '2005年08月15日 10時00分27秒';
    $sd   = new Sabel_Date($time);
    $sd->setFormat(Sabel_Date::JP);
    $this->assertEquals($sd->getDateTime(), '2005年08月15日 10時00分27秒');
    $this->assertEquals($sd->getDate(), '2005年08月15日');
    $this->assertEquals($sd->getTime(), '10時00分27秒');

    $sd->incDay();
    $this->assertEquals($sd->getDateTime(), '2005年08月16日 10時00分27秒');
  }

  public function testConvert()
  {
    $time = '2005-08-15 10:00:00';
    $sd   = new Sabel_Date($time);
    $this->assertEquals($sd->getDatetime(), '2005-08-15 10:00:00');

    $sd->setFormat(Sabel_Date::ATOM);
    $this->assertEquals($sd->getDatetime(), '2005-08-15T10:00:00+09:00');

    $sd->setFormat(Sabel_Date::COOKIE);
    $this->assertEquals($sd->getDatetime(), 'Monday, 15-Aug-05 10:00:00 ' . $this->timezone);

    $sd->setFormat(Sabel_Date::ISO);
    $this->assertEquals($sd->getDatetime(), '2005-08-15T10:00:00+0900');

    $sd->setFormat(Sabel_Date::RFC822);
    $this->assertEquals($sd->getDatetime(), 'Mon, 15 Aug 05 10:00:00 +0900');

    $sd->setFormat(Sabel_Date::RFC850);
    $this->assertEquals($sd->getDatetime(), 'Monday, 15-Aug-05 10:00:00 ' . $this->timezone);

    $sd->setFormat(Sabel_Date::RFC1036);
    $this->assertEquals($sd->getDatetime(), 'Mon, 15 Aug 05 10:00:00 +0900');

    $sd->setFormat(Sabel_Date::RFC1123);
    $this->assertEquals($sd->getDatetime(), 'Mon, 15 Aug 2005 10:00:00 +0900');

    $sd->setFormat(Sabel_Date::RFC2822);
    $this->assertEquals($sd->getDatetime(), 'Mon, 15 Aug 2005 10:00:00 +0900');

    $sd->setFormat(Sabel_Date::RFC);
    $this->assertEquals($sd->getDatetime(), 'Mon, 15 Aug 2005 10:00:00 +0900');

    $sd->setFormat(Sabel_Date::RSS);
    $this->assertEquals($sd->getDatetime(), 'Mon, 15 Aug 2005 10:00:00 +0900');

    $sd->setFormat(Sabel_Date::W3C);
    $this->assertEquals($sd->getDatetime(), '2005-08-15T10:00:00+09:00');
  }

  public function testInc()
  {
    $time = '2005-10-20 10:00:00';
    $sd   = new Sabel_Date($time);

    $this->assertEquals($sd->incDay(), '21');
    $this->assertEquals($sd->incDay(), '22');
    $this->assertEquals($sd->incDay(), '23');

    $sd->incMonth(2);
    $sd->incDay(8);
    $this->assertEquals($sd->getDateTime(), '2005-12-31 10:00:00');

    $sd->incHour(13);
    $this->assertEquals($sd->getDateTime(), '2005-12-31 23:00:00');

    $sd->incMinute(59);
    $this->assertEquals($sd->getDateTime(), '2005-12-31 23:59:00');

    $sd->incSecond(59);
    $this->assertEquals($sd->getDateTime(), '2005-12-31 23:59:59');

    $sd->incSecond();
    //$this->assertEquals($sd->getDateTime(), 'A HAPPY NEW YEAR!!');
    $this->assertEquals($sd->getDateTime(), '2006-01-01 00:00:00');
  }

  public function testDec()
  {
    $time = 'Mon, 15 Aug 2005 10:00:00 +0900';
    $sd   = new Sabel_Date($time);

    $this->assertEquals($sd->decDay(), '14');
    $this->assertEquals($sd->decDay(), '13');
    $this->assertEquals($sd->decDay(), '12');

    $sd->decMonth(7);
    $sd->decDay(11);
    $this->assertEquals($sd->getDateTime(), '2005-01-01 10:00:00');

    $sd->decHour(9);
    $this->assertEquals($sd->getDateTime(), '2005-01-01 01:00:00');

    $sd->decMinute(59);
    $this->assertEquals($sd->getDateTime(), '2005-01-01 00:01:00');

    $sd->decSecond(59);
    $this->assertEquals($sd->getDateTime(), '2005-01-01 00:00:01');

    $sd->decSecond(2);
    $this->assertEquals($sd->getDateTime(), '2004-12-31 23:59:59');
  }

  public function testWeek()
  {
    $time = 'Sun, 14 Aug 2005 10:00:00 ' . $this->timezone;
    $sd   = new Sabel_Date($time);

    $this->assertEquals($sd->getWeek(), 'Sunday');
    $sd->incDay();
    $this->assertEquals($sd->getWeek(), 'Monday');
    $sd->incDay();
    $this->assertEquals($sd->getWeek(), 'Tuesday');
    $sd->incDay();
    $this->assertEquals($sd->getWeek(), 'Wednesday');
    $sd->incDay();
    $this->assertEquals($sd->getWeek(), 'Thursday');
    $sd->incDay();
    $this->assertEquals($sd->getWeek(), 'Friday');
    $sd->incDay();
    $this->assertEquals($sd->getWeek(), 'Saturday');

    $sd->decDay(6);

    $this->assertEquals($sd->getShortWeek(), 'Sun');
    $sd->incDay();
    $this->assertEquals($sd->getShortWeek(), 'Mon');
    $sd->incDay();
    $this->assertEquals($sd->getShortWeek(), 'Tue');
    $sd->incDay();
    $this->assertEquals($sd->getShortWeek(), 'Wed');
    $sd->incDay();
    $this->assertEquals($sd->getShortWeek(), 'Thu');
    $sd->incDay();
    $this->assertEquals($sd->getShortWeek(), 'Fri');
    $sd->incDay();
    $this->assertEquals($sd->getShortWeek(), 'Sat');
  }

  public function test__get()
  {
    $time = '2005-08-05T02:10:10+09:00';
    $sd   = new Sabel_Date($time);

    $this->assertEquals($sd->year   , '2005');
    $this->assertEquals($sd->s_year , '05');
    $this->assertEquals($sd->month  , '08');
    $this->assertEquals($sd->s_month, '8');
    $this->assertEquals($sd->day    , '05');
    $this->assertEquals($sd->s_day  , '5');
    $this->assertEquals($sd->hour   , '02');
    $this->assertEquals($sd->s_hour , '2');
    $this->assertEquals($sd->minute , '10');
    $this->assertEquals($sd->second , '10');
  }
}
