<?php

/**
 * abstract testcase for processor tests.
 * using classes: sabel.Bus, sabel.storage.InMemory
 *
 * @category  Processor
 * @author    Ebine Yutaka <ebine.yutaka@sabel.jp>
 */
class Test_Processor_Abstract extends SabelTestCase
{
  protected $bus = null;
  
  public function setUp()
  {
    $this->bus = new Sabel_Bus();
    $this->bus->set("storage", new Sabel_Storage_InMemory());
    $this->bus->setConfig("map",   new TestMapConfig());
    $this->bus->setConfig("addon", new TestAddonConfig());
  }
}
