<?php

namespace DVD\Event;

use DVD\Request;
use DVD\Cassette;
use DVD\Configuration;
use DVD\Storage;
use DVD\Response;

class BeforeRecordEventTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var BeforeRecordEvent
     */
    private $event;

    protected function setUp()
    {
        $this->event = new BeforeRecordEvent(
            new Request('GET', 'http://example.com'),
            new Response(200),
            new Cassette('test', new Configuration(), new Storage\Blackhole())
        );
    }

    public function testGetRequest()
    {
        $this->assertInstanceOf(Request::class, $this->event->getRequest());
    }

    public function testGetResponse()
    {
        $this->assertInstanceOf(Response::class, $this->event->getResponse());
    }

    public function testGetCassette()
    {
        $this->assertInstanceOf(Cassette::class, $this->event->getCassette());
    }
}
