<?php

namespace DVD\Util;

use DVD\Response;
use DVD\Request;

class HttpClientTest extends \PHPUnit_Framework_TestCase
{
    public function testCreateHttpClient()
    {
        $this->assertInstanceOf(HttpClient::class, new HttpClient());
    }

    public function testCreateHttpClientWithMock()
    {
        $this->assertInstanceOf(HttpClient::class, new HttpClient());
    }
}
