<?php

class SabelDIContainer
{
  public static function parseClassDependencyStructure($className, &$structure)
  {
    if (is_null($className)) throw new Exception("class name is null");
    
    $paths = array('app/commons/models/', 'app/modules/staff/models/', 'app/modules/user/models/');
    
    $hasClassPath = (strpos($className, '_') === false) ? true : false;
    
    if ($hasClassPath) {
      $pathElements = explode('_', $className);
      $classFileName = array_pop($pathElements) . '.php';
      $realPathElements = array();
      
      foreach ($pathElements as $peidx => $pathElement) {
        $realPathElements[] = strtolower($pathElement);
      }
      $realPathElements[] = $classFileName;
      
      $realPath = implode('/', $realPathElements);
      
      foreach ($paths as $pathidx => $path) {
        if (!class_exists($className)) {
          require_once($path . $realPath);
          break;
        }
      }
    } else {
      foreach ($paths as $pathidx => $path) {
        if (!class_exists($className)) {
          if (is_file($path . $className . '.php')) {
            require_once($path . $className . '.php');
            break;
          }
        }
      }
    }
    
    $refClass = new ReflectionClass($className);
    
    $structure[$className]['type'] = SabelDIContainer::getClassType($refClass);
    $refMethods = $refClass->getMethods();
    
    foreach ($refMethods as $refMethodsIdx => $refMethod) {
      foreach ($refMethod->getParameters() as $paramidx => $parameter) {
        $isClass = ($paramClassRef = $parameter->getClass()) ? true : false;
        if ($isClass) {
          $paramClassName = $paramClassRef->getName();
          
          // call self recursive process.
          if (!is_null($paramClassName)) {
            self::parseClassDependencyStructure($paramClassName, $structure);
          }
          
          $type = SabelDIContainer::getClassType($paramClassRef);
            
          $structure[$className][$refMethod->getName()][$paramClassName] = 
            array('type'   => $type,
                  'name'   => $paramClassRef->getName(),
                  'define' => $structure[$paramClassName]);
        } else {
          $structure[$className][$refMethod->getName()][$parameter->getName()] = 
            array('type' => 'parameter', 'name' => $parameter->getName());
        }
      }
    }
  }
  
  public static function getClassType($reflectionClass)
  {
    if ($reflectionClass->isInterface()) {
      $type = 'interface';
    } else if ($reflectionClass->isAbstract()) {
      $type = 'abstract';
    } else if ($reflectionClass->isInstantiable()) {
      $type = 'class';
    } else {
      $type = 'unknown';
    }
    
    return $type;
  }
}

?>