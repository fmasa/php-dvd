<?php

namespace DVD\Storage;

class BlackholeTest extends \PHPUnit_Framework_TestCase
{
    protected $storage;

    public function setUp()
    {
        $this->storage = new Blackhole();
    }

    public function testStoreRecordingIsCallable()
    {
        $this->assertNull($this->storage->storeRecording(array('empty or not, we don\'t care')));

        $this->assertSame(0, iterator_count($this->storage));
    }
}
