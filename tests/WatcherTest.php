<?php

use FsWatcher\Watcher\Linux,
    Sh\Sh;

class WatcherTest extends \PHPUnit_Framework_TestCase
{
    private $counterOnSave = 0;
    private $counterOnDelete = 0;
    private $counterOnCreate = 0;
    private $emmiter;

    public function setUp()
    {
        $this->emmiter = new Evenement\EventEmitter();
        $watcher = new Linux($this->getMock('Sh\Sh'), $this->emmiter);
        $watcher->registerExtensionToWatch('php');

        $watcher->onSave(function () {
                $this->counterOnSave++;
            });
        $watcher->onCreate(function () {
                $this->counterOnCreate++;
            });
        $watcher->onDelete(function () {
                $this->counterOnDelete++;
            });
    }

    public function testOnSave()
    {
        $this->assertEquals(0, $this->counterOnSave);
        $this->emmit("./ MOVED_TO file.php");
        $this->assertEquals(1, $this->counterOnSave);
        $this->emmit("./ CLOSE_WRITE file.php");
        $this->assertEquals(2, $this->counterOnSave);
        $this->emmit("./ MOVE file.php");
        $this->assertEquals(3, $this->counterOnSave);
        $this->emmit("./ MODIFY file.php");
        $this->assertEquals(4, $this->counterOnSave);
        $this->emmit('./ XXX,MODIFY file.php');
        $this->assertEquals(5, $this->counterOnSave);
    }

    public function testOnSaveNonPhpFile()
    {
        $this->assertEquals(0, $this->counterOnSave);
        $this->emmit("./ MOVED_TO file.json");
        $this->assertEquals(0, $this->counterOnSave);
    }

    public function testOnSaveFileNamesWithSpaces()
    {
        $this->assertEquals(0, $this->counterOnSave);
        $this->emmit("./ MOVED_TO file with spaces.php");
        $this->assertEquals(1, $this->counterOnSave);
    }

    public function testOnSaveWithMoreThanOneActionPerLine()
    {
        $this->assertEquals(0, $this->counterOnSave);
        $this->emmit("./ XXX,MOVED_TO file.php");
        $this->assertEquals(1, $this->counterOnSave);
    }

    public function testOnSaveNonSaveActions()
    {
        $this->assertEquals(0, $this->counterOnSave);
        $this->emmit("./ CREATE file.php");
        $this->assertEquals(0, $this->counterOnSave);
    }

    public function testOnDelete()
    {
        $this->assertEquals(0, $this->counterOnDelete);
        $this->emmit("./ DELETE Linux file.php");
        $this->assertEquals(1, $this->counterOnDelete);
    }

    public function testOnCreate()
    {
        $this->assertEquals(0, $this->counterOnCreate);
        $this->emmit("./ CREATE Linux file.php");
        $this->assertEquals(1, $this->counterOnCreate);
    }

    private function emmit($buffer)
    {
        $this->emmiter->emit('output', array($buffer));
    }
}