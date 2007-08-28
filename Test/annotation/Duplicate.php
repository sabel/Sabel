<?php

/**
 * test for duplicate entry
 *
 * @annotation dupOne
 * @annotation dupTwo two
 * @annotation dupThree
 */
class Test_Annotation_Dupulicate
{
  /**
   * this is annotation test
   *
   * @dup dupOne
   * @dup dupTwo
   */
  public function testMethod()
  {
  }
}