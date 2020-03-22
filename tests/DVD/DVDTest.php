<?php

namespace DVD;

use Symfony\Component\EventDispatcher\Event;
use org\bovigo\vfs\vfsStream;
use DVD\Util\SoapClient;

/**
 * Test integration of PHPDVD with PHPUnit.
 */
class DVDTest extends \PHPUnit_Framework_TestCase
{
    public static function setupBeforeClass()
    {
        DVD::configure()
            ->setCassettePath('tests/fixtures')
            ->setStorage('json');
    }

    public function testUseStaticCallsNotInitialized()
    {
        DVD::configure()->enableLibraryHooks(array('stream_wrapper'));
        $this->setExpectedException(
            DVDException::class,
            'Please turn on DVD before inserting a cassette, use: DVD::turnOn()'
        );
        DVD::insertCassette('some_name');
    }

    public function testShouldInterceptStreamWrapper()
    {
        DVD::configure()->enableLibraryHooks(array('stream_wrapper'));
        DVD::turnOn();
        DVD::insertCassette('unittest_streamwrapper_test');
        $result = file_get_contents('http://example.com');
        $this->assertEquals('This is a stream wrapper test dummy.', $result, 'Stream wrapper call was not intercepted.');
        DVD::eject();
        DVD::turnOff();
    }

    public function testShouldInterceptCurlLibrary()
    {
        DVD::configure()->enableLibraryHooks(array('curl'));
        DVD::turnOn();
        DVD::insertCassette('unittest_curl_test');

        $output = $this->doCurlGetRequest('http://google.com/');

        $this->assertEquals('This is a curl test dummy.', $output, 'Curl call was not intercepted.');
        DVD::eject();
        DVD::turnOff();
    }

    private function doCurlGetRequest($url)
    {
        $ch = \DVD\LibraryHooks\CurlHook::curl_init();
        \DVD\LibraryHooks\CurlHook::curl_setopt($ch, CURLOPT_URL, $url);
        \DVD\LibraryHooks\CurlHook::curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        \DVD\LibraryHooks\CurlHook::curl_setopt($ch, CURLOPT_POST, false);
        $output = \DVD\LibraryHooks\CurlHook::curl_exec($ch);
        \DVD\LibraryHooks\CurlHook::curl_close($ch);

        return $output;
    }

    public function testShouldInterceptSoapLibrary()
    {
        DVD::configure()->enableLibraryHooks(array('soap'));
        DVD::turnOn();
        DVD::insertCassette('unittest_soap_test');

        $client = new SoapClient('https://raw.githubusercontent.com/fmasa/php-dvd/master/tests/fixtures/soap/wsdl/weather.wsdl', array('soap_version' => SOAP_1_2));
        $actual = $client->GetCityWeatherByZIP(array('ZIP' => '10013'));
        $temperature = $actual->GetCityWeatherByZIPResult->Temperature;

        $this->assertEquals('1337', $temperature, 'Soap call was not intercepted.');
        DVD::eject();
        DVD::turnOff();
    }

    public function testShouldNotInterceptCallsToDevUrandom()
    {
        if ('\\' === DIRECTORY_SEPARATOR) {
            $this->markTestSkipped('/dev/urandom is not supported on Windows');
        }

        DVD::configure()->enableLibraryHooks(array('stream_wrapper'));
        DVD::turnOn();
        DVD::insertCassette('unittest_urandom_test');

        // Just trying to open this will cause an exception if you're using is_file to filter
        // which paths to intercept.
        $output = file_get_contents('/dev/urandom', false, null, 0, 16);

        DVD::eject();
        DVD::turnOff();
    }

    public function testShouldThrowExceptionIfNoCassettePresent()
    {
        $this->setExpectedException(
            'BadMethodCallException',
            'Invalid http request. No cassette inserted. Please make sure to insert '
            . "a cassette in your unit test using DVD::insertCassette('name');"
        );

        DVD::configure()->enableLibraryHooks(array('stream_wrapper'));
        DVD::turnOn();
        // If there is no cassette inserted, a request should throw an exception
        file_get_contents('http://example.com');
        DVD::turnOff();
    }

    public function testInsertMultipleCassettes()
    {
        $this->configureVirtualCassette();

        DVD::turnOn();
        DVD::insertCassette('unittest_cassette1');
        DVD::insertCassette('unittest_cassette2');
        // TODO: Check of cassette was changed
    }

    public function testDoesNotBlockThrowingExceptions()
    {
        $this->configureVirtualCassette();

        DVD::turnOn();
        $this->setExpectedException('InvalidArgumentException');
        DVD::insertCassette('unittest_cassette1');
        throw new \InvalidArgumentException('test');
    }

    private function configureVirtualCassette()
    {
        vfsStream::setup('testDir');
        DVD::configure()->setCassettePath(vfsStream::url('testDir'));
    }

    public function testShouldSetAConfiguration()
    {
        DVD::configure()->setCassettePath('tests');
        DVD::turnOn();
        $this->assertEquals('tests', DVD::configure()->getCassettePath());
        DVD::turnOff();
    }

    public function testShouldDispatchBeforeAndAfterPlaybackWhenCassetteHasResponse()
    {
        DVD::configure()
            ->enableLibraryHooks(array('curl'));
        $this->recordAllEvents();
        DVD::turnOn();
        DVD::insertCassette('unittest_curl_test');

        $this->doCurlGetRequest('http://google.com/');

        $this->assertEquals(
            array(DVDEvents::BEFORE_PLAYBACK, DVDEvents::AFTER_PLAYBACK),
            $this->getRecordedEventNames()
        );
        DVD::eject();
        DVD::turnOff();
    }

    public function testShouldDispatchBeforeAfterHttpRequestAndBeforeRecordWhenCassetteHasNoResponse()
    {
        vfsStream::setup('testDir');
        DVD::configure()
            ->setCassettePath(vfsStream::url('testDir'))
            ->enableLibraryHooks(array('curl'));
        $this->recordAllEvents();
        DVD::turnOn();
        DVD::insertCassette('virtual_cassette');

        $this->doCurlGetRequest('http://google.com/');

        $this->assertEquals(
            array(
                DVDEvents::BEFORE_PLAYBACK,
                DVDEvents::BEFORE_HTTP_REQUEST,
                DVDEvents::AFTER_HTTP_REQUEST,
                DVDEvents::BEFORE_RECORD
            ),
            $this->getRecordedEventNames()
        );
        DVD::eject();
        DVD::turnOff();
    }

    private function recordAllEvents()
    {
        $allEventsToListen = array(
            DVDEvents::BEFORE_PLAYBACK,
            DVDEvents::AFTER_PLAYBACK,
            DVDEvents::BEFORE_HTTP_REQUEST,
            DVDEvents::AFTER_HTTP_REQUEST,
            DVDEvents::BEFORE_RECORD,
        );
        foreach ($allEventsToListen as $eventToListen) {
            DVD::getEventDispatcher()->addListener($eventToListen, array($this, 'recordEvent'));
        }
    }

    public function recordEvent(Event $event, $eventName)
    {
        $this->events[$eventName] = $event;
    }

    private function getRecordedEventNames()
    {
        return array_keys($this->events);
    }
}
