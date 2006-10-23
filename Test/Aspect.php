<?php

/**
 * TestCase of sabel.aspect.*
 *
 * @author Mori Reo <mori.reo@gmail.com>
 */
class Test_Aspect extends PHPUnit2_Framework_TestCase
{
  public static function suite()
  {
    return new PHPUnit2_Framework_TestSuite("Test_Aspect");
  }
  
  public function testDynamicProxy()
  {
    $before = create_function('$target, $method', 'return $method->getName();');
    $after  = create_function('$target, $method, $result', 'return $result;');
    $both   = create_function('$target, $method, $result', 'return null;');
    
    $customer = new Sabel_Aspect_DynamicProxy(new Test_Aspect_Customers());
    $customer->beforeAspect('before', $before);
    $customer->afterAspect('after', $after);
    $customer->bothAspect('both', $both);
    $customer->getOrder();
    
    $this->assertEquals($customer->beforeResult('before'), 'getOrder');
    $this->assertEquals($customer->afterResult('after'), 'order');
    $this->assertEquals($customer->bothResult('both'), null);
  }
  
  public function testNestedOverloads()
  {
    $arg     = '$target, $method, $result';
    $routine = 'return $result;';
    
    $ol = new Sabel_Aspect_DynamicProxy(new Test_Aspect_Overloads());
    $ol->afterAspect('after', create_function($arg, $routine));
    $ol->callOverloads('test');
    $this->assertEquals('callOverloads', $ol->afterResult('after'));
    
    $ol->callOverloadsTwo('test');
    $this->assertEquals('callOverloadsTwo', $ol->afterResult('after'));
  }
  
  public function testIntertypeDeclaration()
  {
    $obj = new Test_Aspect_IntertypeDeclarator(new Test_Aspect_IntertypeTarget());
    $this->assertEquals('test',   $obj->added());
    $this->assertEquals('exists', $obj->exists());
  }
  
  public function testPointcut()
  {
    $aspect    = new Test_AspectOne();
    $aspectTwo = new Test_AspectTwo();
    
    $matcher = new Sabel_Aspect_Matcher();
    $matcher->add($aspect->pointcut());
    $matcher->add($aspectTwo->pointcut());
    
    $matches = $matcher->findMatch(array('class'=>'Target', 'method'=>'doSomething'));
    $this->assertTrue($matches->matched('Test_AspectOne'));
    $this->assertTrue($matches->matched('Test_AspectTwo'));
    
    $matches = $matcher->findMatch(array('class'=>'Target', 'method'=>'doWhat'));
    $this->assertTrue($matches->matched('Test_AspectOne'));
  }
  
  public function testAspect()
  {
    $aspects = Sabel_Aspect_Aspects::singleton();
    $aspects->add(new Test_AspectOne());
    $aspects->add(new Test_AspectTwo());
    $maches = $aspects->findMatch(array('method' => 'doSome'));
    $this->assertTrue($maches->matched('Test_AspectOne'));
    $target = new Sabel_Aspect_Proxy(new Target());
    $target->doSomething();
  }
  
  public function testAspectAfterThrowing()
  {
    $aspects = Sabel_Aspect_Aspects::singleton();
    $aspects->add(new ServerHandler());
    
    $rack = new Sabel_Aspect_Proxy(new Rack());
    $firstMount  = $rack->mount(new Server('XEON'));
    $secondMount = $rack->mount(new Server('Opteron'));
    
    $this->assertTrue($firstMount);
    $this->assertEquals('rack space full', $secondMount);
    $this->assertEquals(1, ServerStock::getNumberOfStock());
    $this->assertEquals('Opteron', ServerStock::pop()->getName());
  }
}

// test classes for testAspectAfterThrowing
{
  class NoRackSpaceException extends Exception { }
  
  class Rack
  {
    protected $space = 41;
    
    public function mount($server)
    {
      if ($this->space >= 42) {
        throw new NoRackSpaceException('rack space full');
      }
      
      $this->space++;
      return true;
    }
  }
  
  class ServerStock
  {
    protected static $counter = 0;
    protected static $stock = array();
    
    public static function add($server)
    {
      self::$counter++;
      array_push(self::$stock, $server);
    }
    
    public static function getNumberOfStock()
    {
      return self::$counter;
    }
    
    public static function pop()
    {
      return array_pop(self::$stock);
    }
  }
  
  class Server
  {
    protected $name = '';
    
    public function __construct($name)
    {
      $this->name = $name;
    }
    
    public function getName()
    {
      return $this->name;
    }
  }
  
  class ServerHandler
  {
    public function pointcut()
    {
      return Sabel_Aspect_Pointcut::create('ServerHandler', $this)->setMethodRegex('mount')
                                                                  ->asAfter();
    }
    
    public function afterThrowing(NoRackSpaceException $e, $arg, $target) {
      ServerStock::add($arg[0]);
      return $e->getMessage();
    }
  }
}


class Target
{
  public function doSomething()
  {
    
  }
}

class Test_AspectOne
{
  public function pointcut()
  {
    return Sabel_Aspect_Pointcut::create('Test_AspectOne', $this)->setMethodRegex('do.*')
                                                                 ->asAfter();
  }
  
  public function around()
  {
    
  }
  
  public function before()
  {
    
  }
  
  public function after()
  {
    
  }
}

class Test_AspectTwo
{
  public function pointcut()
  {
    return Sabel_Aspect_Pointcut::create('Test_AspectTwo', $this)->setMethodRegex('doS.*')
                                                                 ->asBefore();
  }
  
  public function around()
  {
    
  }
  
  public function before()
  {
    
  }
  
  public function after()
  {
    
  }
}

class Test_Aspect_IntertypeDeclarator extends Sabel_Aspect_DynamicProxy
{
  public function added()
  {
    return 'test';
  }
}

class Test_Aspect_IntertypeTarget
{
  public function exists()
  {
    return 'exists';
  }
}

class Test_Aspect_Customers
{
  public function getOrder()
  {
    return 'order';
  }
}

class Test_Aspect_Overloads
{
  public function __call($method, $arg)
  {
    return $method;
  }
}