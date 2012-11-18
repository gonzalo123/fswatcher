<?php

use FsWatcher\Watcher;

class WatcherTest extends \PHPUnit_Framework_TestCase
{
    private $watcher;
    private $onSaveCounter = 0;
    private $onDeleteCounter = 0;
    private $onCreateCounter = 0;

    public function setUp()
    {
        $osWatcher = $this->getMockBuilder('FsWatcher\Watcher\Iface')
            ->disableOriginalConstructor()
            ->getMock();
        $osWatcher->expects($this->any())->method('registerExtensionToWatch')->will($this->returnSelf());
        $osWatcher->expects($this->any())->method('onSave')->will($this->returnCallback(function () {
            $this->onSaveCounter++;
        }));
        $osWatcher->expects($this->any())->method('onDelete')->will($this->returnCallback(function () {
            $this->onDeleteCounter++;
        }));
        $osWatcher->expects($this->any())->method('onCreate')->will($this->returnCallback(function () {
            $this->onCreateCounter++;
        }));

        $this->watcher = new Watcher($osWatcher);
        $this->watcher->registerExtensionToWatch('php');
    }

    public function testCallOnSave()
    {
        $this->assertEquals(0, $this->onSaveCounter);
        $this->watcher->onSave(function () {});
        $this->assertEquals(1, $this->onSaveCounter);
    }

    public function testCallOnDelete()
    {
        $this->assertEquals(0, $this->onDeleteCounter);
        $this->watcher->onDelete(function () {});
        $this->assertEquals(1, $this->onDeleteCounter);
    }

    public function testCallOnCreate()
    {
        $this->assertEquals(0, $this->onCreateCounter);
        $this->watcher->onCreate(function () {});
        $this->assertEquals(1, $this->onCreateCounter);
    }
}