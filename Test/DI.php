<?php

/**
 * test case for Sabel LW DI Container
 *
 * @author Mori Reo <mori.reo@gmail.com>
 */
class Test_DI extends SabelTestCase
{
  protected $c = null;
  
  public static function suite()
  {
    return self::createSuite("Test_DI");
  }
  
  // test case for class A depend class B.
  public function testDependencyResolve()
  {
    $c = new Sabel_Container_DI();
    
    $person = $c->load('Person');
    $this->assertEquals(15, $person->howOldAreYou());
  }
  
  public function testSetterInjection()
  {
    $c = new Sabel_Container_DI();
    $c->depends("Person", "FrastrationCalculator", "Setter");
    $person = $c->load("Person");
    $this->assertEquals(10, $person->getFrastration());
  }
}

class Person
{
  protected $age = null;
  
  /**
   * @implementation FrastrationCalculator
   * @setter setFrastration
   */
  protected $frastration = null;
  
  /**
   *
   */
  public function __construct(Age $age)
  {
    $this->age = $age;
  }
  
  public function howOldAreYou()
  {
    return $this->age->getAge();
  }
  
  public function getFrastration()
  {
    if (!is_object($this->frastration)) throw new Exception(var_export($this->frastration, 1));
    return $this->frastration->calc($this);
  }
  
  public function setFrastrationCalculator($f)
  {
    $this->frastration = $f;
  }
}

class Integer
{
  private $self = 0;
  
  public function set($num)
  {
    $this->self = $num;
  }
  
  public function add($num)
  {
    return $this->self + $num;
  }
}

class FrastrationCalculator
{
  private $int = null;
  
  public function __construct(Integer $int)
  {
    $this->int = $int;
  }
  
  public function calc($obj)
  {
    $this->int->set(5);
    $this->int->add(5);
    return 10;
  }
}

class Age
{
  protected $age = 15;
  
  public function __construct()
  {
  }
  
  public function getAge()
  {
    return $this->age;
  }
  
  public function setAge($age = 0)
  {
    $this->age = $age;
  }
}