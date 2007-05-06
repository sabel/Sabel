<?php

require ("sabel/Container.php");

/**
 * TestCase for Sabel Container
 *
 * @author Mori Reo <mori.reo@gmail.com>
 */
class Test_Container extends SabelTestCase
{
  public static function suite()
  {
    return self::createSuite("Test_Container");
  }
  
  // test case for class A depend class B.
  public function testDependencyResolve()
  {
    $c = new Sabel_Container_DI();
    
    $person = $c->load("Person");
    $this->assertEquals(15, $person->howOldAreYou());
  }
  
  /*
  public function estSetterInjection()
  {
    $c = new Sabel_Container_DI();
    $c->depends("Person", "FrastrationCalculator", "Setter");
    $person = $c->load("Person");
    $this->assertEquals(10, $person->calc());
  }
  */
  
  public function testInjection()
  {
    $injector = Sabel_Container::injector(new Config());
    $person = $injector->getInstance("Person");
    $this->assertEquals(10, $person->calc());
  }
  
  public function testConstructorInjection()
  {
    $injector = Sabel_Container::injector(new ConstructConfig());
    $car = $injector->getInstance("Car");
    $this->assertTrue(is_object($car->getEngine()));
  }
  
  public function testStrLiteralConstructorInjection()
  {
    $injector = Sabel_Container::injector(new StrLiteralConstructConfig());
    $car = $injector->getInstance("Car");
    $this->assertEquals("this is engine", $car->getEngine());
  }
  
  public function testNumLiteralConstructorInjection()
  {
    $injector = Sabel_Container::injector(new NumLiteralConstructConfig());
    $car = $injector->getInstance("Car");
    $this->assertEquals(123, $car->getEngine());
  }
  
  public function testBoolLiteralConstructorInjection()
  {
    $injector = Sabel_Container::injector(new BoolLiteralConstructConfig());
    $car = $injector->getInstance("Car");
    $this->assertTrue($car->getEngine());
  }
  
  public function testBadInjectionConfig()
  {
    $injector = Sabel_Container::injector(new BadClassNameConfig());
    
    try {
      $person = $injector->getInstance("Person");
      $this->fail();
    } catch (Sabel_Exception_Runtime $e) {
      $this->assertEquals("BadCalculator does not exists", $e->getMessage());
    }
  }
}

class ConstructConfig extends Sabel_Container_Injection
{
  public function configure()
  {
    $this->bindConstruct("Car")->construct("Engine");
  }
}
class StrLiteralConstructConfig extends Sabel_Container_Injection
{
  public function configure()
  {
    $this->bindConstruct("Car")->construct("this is engine");
  }
}
class NumLiteralConstructConfig extends Sabel_Container_Injection
{
  public function configure()
  {
    $this->bindConstruct("Car")->construct(123);
  }
}
class BoolLiteralConstructConfig extends Sabel_Container_Injection
{
  public function configure()
  {
    $this->bindConstruct("Car")->construct(true);
  }
}
class Car
{
  private $engine = null;
  
  public function __construct($engine)
  {
    $this->engine = $engine;
  }
  
  public function getEngine()
  {
    return $this->engine;
  }
}
class Engine
{
  public function run(){}
}

class Config extends Sabel_Container_Injection
{
  public function configure()
  {
    $this->bind("Calculator")->to("FrastrationCalculator");
  }
}

class BadClassNameConfig extends Sabel_Container_Injection
{
  public function configure()
  {
    $this->bind("Calculator")->to("BadCalculator");
  }
}

/****************************************************************************/

class Person
{
  protected $age  = null;
  protected $calc = null;
  
  public function __construct(Age $age)
  {
    $this->age = $age;
  }
  
  public function howOldAreYou()
  {
    return $this->age->getAge();
  }
  
  public function setCalculator(Calculator $c)
  {
    $this->calc = $c;
  }
  
  public function getCalculator()
  {
    if (!is_object($this->calc)) {
      throw new Exception(var_export($this->calc, 1));
    }
    
    return $this->calc;
  }
  
  public function calc()
  {
    return $this->calc->calc($this);
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

interface Calculator
{
  public function calc($obj);
}

class FrastrationCalculator implements Calculator
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
