<?php

namespace DVD\Event;

use DVD\Request;

class BeforeHttpRequestEventTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var BeforeHttpRequestEvent
     */
    private $event;

    protected function setUp()
    {
        $this->event = new BeforeHttpRequestEvent(new Request('GET', 'http://example.com'));
    }

    public function testGetRequest()
    {
        $this->assertInstanceOf(Request::class, $this->event->getRequest());
    }
}
