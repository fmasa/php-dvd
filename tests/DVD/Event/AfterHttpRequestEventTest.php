<?php

namespace DVD\Event;

use DVD\Request;
use DVD\Response;

class AfterHttpRequestEventTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var AfterHttpRequestEvent
     */
    private $event;

    protected function setUp()
    {
        $this->event = new AfterHttpRequestEvent(new Request('GET', 'http://example.com'), new Response(200));
    }

    public function testGetRequest()
    {
        $this->assertInstanceOf(Request::class, $this->event->getRequest());
    }

    public function testGetResponse()
    {
        $this->assertInstanceOf(Response::class, $this->event->getResponse());
    }
}
