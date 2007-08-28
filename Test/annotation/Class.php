<?php

/**
 * class annotation
 *
 * @annotation class
 */
class Test_Annotation_Class
{
  /**
   * this is annotation test
   *
   * @normal test1
   * @ignoreSpace   test2
   * @array      test4 elem1 "a: index"
   */
  public function testMethod($test, $test = null)
  {
  }
  
  /**
   * this is annotation test
   *
   * @normal test1
   * @ignoreSpace   test2
   * @array      test4 elem1 elem2 elem3
   */
  public function testMethodTwo($test, $test = null)
  {
  }
}
