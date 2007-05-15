<?php

require ("sabel/Container.php");

/**
 * TestCase of sabel container
 *
 * @category   Test
 * @package    org.sabel.testcase
 * @author     Mori Reo <mori.reo@gmail.com>
 * @copyright  2002-2006 Mori Reo <mori.reo@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Test_Container extends SabelTestCase
{
  public static function suite()
  {
    return self::createSuite("Test_Container");
  }
  
  public function testInjection()
  {
    $injector = Sabel_Container::injector(new Config());
    $person = $injector->newInstance("Person");
    $this->assertEquals(10, $person->calc());
  }
  
  public function testConstructorInjection()
  {
    $injector = Sabel_Container::injector(new ConstructConfig());
    $car = $injector->newInstance("Car");
    $this->assertTrue(is_object($car->getEngine()));
  }
  
  public function testStrLiteralConstructorInjection()
  {
    $injector = Sabel_Container::injector(new StrLiteralConstructConfig());
    $car = $injector->newInstance("Car");
    $this->assertEquals("this is engine", $car->getEngine());
  }
  
  public function testNumLiteralConstructorInjection()
  {
    $injector = Sabel_Container::injector(new NumLiteralConstructConfig());
    $car = $injector->newInstance("Car");
    $this->assertEquals(123, $car->getEngine());
  }
  
  public function testBoolLiteralConstructorInjection()
  {
    $injector = Sabel_Container::injector(new BoolLiteralConstructConfig());
    $car = $injector->newInstance("Car");
    $this->assertTrue($car->getEngine());
  }
  
  public function testBadInjectionConfig()
  {
    $injector = Sabel_Container::injector(new BadClassNameConfig());
    
    try {
      $person = $injector->newInstance("Person");
      $this->fail();
    } catch (Sabel_Exception_Runtime $e) {
      $this->assertEquals("BadCalculator does not exists", $e->getMessage());
    }
  }
  
  public function testNoInjectionConfigToConstructer()
  {
    try {
      $injector = Sabel_Container::injector(new StdClass());
      $this->fail();
    } catch (Sabel_Exception_Runtime $e) {
      $msg = "must be Sabel_Container_Injection";
      $this->assertEquals($msg, $e->getMessage());
    }
  }
  
  public function testMultipleConstructerInjection()
  {
    $injector = Sabel_Container::injector(new MultipleConstructConfig());
    
    $oil    = new EngineOil("normal");
    $engine = new MultiEngine($oil);
    $car    = new MultiCar($engine, "multiple");
    
    $injCar = $injector->newInstance("MultiCar");
    
    $this->assertEquals($car, $injCar);
  }
  
  public function testSpecificSetter()
  {
    $injector = Sabel_Container::injector(new SpecificSetterConfig());
    $instance = $injector->newInstance("SpecificSetter");
    
    $engineOil = new EngineOil("specific");
    $specific  = new SpecificSetter();
    $specific->setSpecificSetter($engineOil);
    
    $this->assertEquals($instance, $specific);
  }
  
  public function testApplyAspect()
  {
    $trace = new Trace();
    $aspectConfig = new AspectConfig($trace);
    
    $injector = Sabel_Container::injector($aspectConfig);
    $instance = $injector->newInstance("AspectTarget");
    $instance->run("test");
    
    $this->assertEquals("test", $trace->getArgument());
  }
}

class AspectConfig extends Sabel_Container_Injection
{
  private $trace = null;
  
  public function __construct($trace)
  {
    $this->trace = $trace;
  }
  public function configure()
  {
    $this->aspect("AspectTarget")->apply($this->trace)->method("run");
  }
  
  public function getTrace()
  {
    return $this->trace;
  }
}
class AspectTarget
{
  public function run($parameter)
  {
    return $parameter;
  }
}
class Trace
{
  private $argument = "";
  
  public function after($joinpoint)
  {
    $this->argument = $joinpoint->getArgument(0);
  }
  
  public function getArgument()
  {
    return $this->argument;
  }
}

class SpecificSetter
{
  private $oil = null;
  public function setSpecificSetter(Oil $oil)
  {
    $this->oil = $oil;
  }
}
class SpecificSetterConfig extends Sabel_Container_Injection
{
  public function configure()
  {
    $this->construct("EngineOil")->with("specific");
    $this->bind("Oil")->to("EngineOil")->setter("setSpecificSetter");
  }
}

class MultipleConstructConfig extends Sabel_Container_Injection
{
  public function configure()
  {
    $this->construct("MultiCar")->with("MultiEngine")
                                ->with("multiple");
                                    
    $this->construct("MultiEngine")->with("Oil");
    $this->construct("EngineOil")->with("normal");
    
    $this->bind("Oil")->to("EngineOil");
  }
}
class MultiCar
{
  private $engine = null;
  private $shaft  = null;
  
  public function __construct($engine, $shaft)
  {
    $this->engine = $engine;
    $this->shaft  = $shaft;
  }
}
class MultiEngine
{
  private $oil = null;
  
  public function __construct($oil)
  {
    $this->oil = $oil;
  }
}
interface Oil
{
}
class EngineOil implements Oil
{
  private $type = "";
  public function __construct($type)
  {
    $this->type = $type;
  }
}

class ConstructConfig extends Sabel_Container_Injection
{
  public function configure()
  {
    $this->construct("Car")->with("Engine");
  }
}
class StrLiteralConstructConfig extends Sabel_Container_Injection
{
  public function configure()
  {
    $this->construct("Car")->with("this is engine");
  }
}
class NumLiteralConstructConfig extends Sabel_Container_Injection
{
  public function configure()
  {
    $this->construct("Car")->with(123);
  }
}
class BoolLiteralConstructConfig extends Sabel_Container_Injection
{
  public function configure()
  {
    $this->construct("Car")->with(true);
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