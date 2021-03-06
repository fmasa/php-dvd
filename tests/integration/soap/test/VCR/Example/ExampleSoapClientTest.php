<?php

namespace DVD\Example;

use org\bovigo\vfs\vfsStream;

/**
 * Converts temperature units from webservicex
 *
 * @link http://www.webservicex.net/New/Home/ServiceDetail/31
 */
class ExampleSoapClientTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        // Configure virtual filesystem.
        vfsStream::setup('testDir');
        \DVD\DVD::configure()->setCassettePath(vfsStream::url('testDir'));
    }

    public function testCallDirectly()
    {
        $actual = $this->callSoap();
        $this->assertInternalType('string', $actual);
        $this->assertEquals('twelve', $actual);
    }

    public function testCallIntercepted()
    {
        $actual = $this->callSoapIntercepted();
        $this->assertInternalType('string', $actual);
        $this->assertEquals('twelve', $actual);
    }

    public function testCallDirectlyEqualsIntercepted()
    {
        $this->assertEquals($this->callSoap(), $this->callSoapIntercepted());
    }

    protected function callSoap()
    {
        $soapClient = new ExampleSoapClient();
        return $soapClient->call(12);
    }

    protected function callSoapIntercepted()
    {
        \DVD\DVD::turnOn();
        \DVD\DVD::insertCassette('test-cassette.yml');
        $result = $this->callSoap();
        \DVD\DVD::turnOff();

        return $result;
    }
}
