<?php

namespace DVD\Event;

use DVD\Request;
use DVD\Cassette;
use DVD\Configuration;
use DVD\Storage;

class BeforePlaybackEventTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var BeforePlaybackEvent
     */
    private $event;

    protected function setUp()
    {
        $this->event = new BeforePlaybackEvent(
            new Request('GET', 'http://example.com'),
            new Cassette('test', new Configuration(), new Storage\Blackhole())
        );
    }

    public function testGetRequest()
    {
        $this->assertInstanceOf(Request::class, $this->event->getRequest());
    }

    public function testGetCassette()
    {
        $this->assertInstanceOf(Cassette::class, $this->event->getCassette());
    }
}
