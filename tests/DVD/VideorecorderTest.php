<?php

namespace DVD;

use DVD\Util\HttpClient;
use lapistano\ProxyObject\ProxyBuilder;
use org\bovigo\vfs\vfsStream;

/**
 * Test Videorecorder.
 */
class VideorecorderTest extends \PHPUnit_Framework_TestCase
{
    public function testCreateVideorecorder()
    {
        $this->assertInstanceOf(
            Videorecorder::class,
            new Videorecorder(new Configuration(), new Util\HttpClient(), DVDFactory::getInstance())
        );
    }

    public function testInsertCassetteEjectExisting()
    {
        vfsStream::setup('testDir');
        $factory = DVDFactory::getInstance();
        $configuration = $factory->get(Configuration::class);
        $configuration->setCassettePath(vfsStream::url('testDir'));
        $configuration->enableLibraryHooks(array());
        $videorecorder = $this->getMockBuilder(Videorecorder::class)
            ->setConstructorArgs(array($configuration, new Util\HttpClient(), DVDFactory::getInstance()))
            ->setMethods(array('eject'))
            ->getMock();

        $videorecorder->expects($this->exactly(2))->method('eject');

        $videorecorder->turnOn();
        $videorecorder->insertCassette('cassette1');
        $videorecorder->insertCassette('cassette2');
        $videorecorder->turnOff();
    }

    public function testHandleRequestRecordsRequestWhenModeIsNewRecords()
    {
        $request = new Request('GET', 'http://example.com', array('User-Agent' => 'Unit-Test'));
        $response = new Response(200, array(), 'example response');
        $client = $this->getClientMock($request, $response);
        $configuration = new Configuration();
        $configuration->enableLibraryHooks(array());
        $configuration->setMode('new_episodes');

        $proxy = new ProxyBuilder('\\' . Videorecorder::class);
        $videorecorder = $proxy
            ->setConstructorArgs(array($configuration, $client, DVDFactory::getInstance()))
            ->setProperties(array('cassette', 'client'))
            ->getProxy();
        $videorecorder->client = $client;
        $videorecorder->cassette = $this->getCassetteMock($request, $response);

        $this->assertEquals($response, $videorecorder->handleRequest($request));
    }

    public function testHandleRequestThrowsExceptionWhenModeIsNone()
    {
        $this->setExpectedException(
            'LogicException',
            "The request does not match a previously recorded request and the 'mode' is set to 'none'. "
            . "If you want to send the request anyway, make sure your 'mode' is set to 'new_episodes'."
        );

        $request = new Request('GET', 'http://example.com', array('User-Agent' => 'Unit-Test'));
        $response = new Response(200, array(), 'example response');
        $client = $this->getMockBuilder(HttpClient::class)->getMock();
        $configuration = new Configuration();
        $configuration->enableLibraryHooks(array());
        $configuration->setMode('none');

        $proxy = new ProxyBuilder('\\' . Videorecorder::class);
        $videorecorder = $proxy
            ->setConstructorArgs(array($configuration, $client, DVDFactory::getInstance()))
            ->setProperties(array('cassette', 'client'))
            ->getProxy();
        $videorecorder->client = $client;

        $videorecorder->cassette = $this->getCassetteMock($request, $response, 'none');

        $videorecorder->handleRequest($request);
    }

    public function testHandleRequestRecordsRequestWhenModeIsOnceAndCassetteIsNew()
    {
        $request = new Request('GET', 'http://example.com', array('User-Agent' => 'Unit-Test'));
        $response = new Response(200, array(), 'example response');
        $client = $this->getClientMock($request, $response);
        $configuration = new Configuration();
        $configuration->enableLibraryHooks(array());
        $configuration->setMode('once');

        $proxy = new ProxyBuilder('\\' . Videorecorder::class);
        $videorecorder = $proxy
            ->setConstructorArgs(array($configuration, $client, DVDFactory::getInstance()))
            ->setProperties(array('cassette', 'client'))
            ->getProxy();
        $videorecorder->client = $client;

        $videorecorder->cassette = $this->getCassetteMock($request, $response, 'once', true);

        $this->assertEquals($response, $videorecorder->handleRequest($request));
    }

    public function testHandleRequestThrowsExceptionWhenModeIsOnceAndCassetteIsOld()
    {
        $this->setExpectedException(
            'LogicException',
            "The request does not match a previously recorded request and the 'mode' is set to 'once'. "
            . "If you want to send the request anyway, make sure your 'mode' is set to 'new_episodes'."
        );

        $request = new Request('GET', 'http://example.com', array('User-Agent' => 'Unit-Test'));
        $response = new Response(200, array(), 'example response');
        $client = $this->getMockBuilder(HttpClient::class)->getMock();
        $configuration = new Configuration();
        $configuration->enableLibraryHooks(array());
        $configuration->setMode('once');

        $proxy = new ProxyBuilder('\\' . Videorecorder::class);
        $videorecorder = $proxy
            ->setConstructorArgs(array($configuration, $client, DVDFactory::getInstance()))
            ->setProperties(array('cassette', 'client'))
            ->getProxy();
        $videorecorder->client = $client;

        $videorecorder->cassette = $this->getCassetteMock($request, $response, 'once', false);

        $videorecorder->handleRequest($request);
    }

    protected function getClientMock($request, $response)
    {
        $client = $this->getMockBuilder(HttpClient::class)->setMethods(array('send'))->getMock();
        $client
            ->expects($this->once())
            ->method('send')
            ->with($request)
            ->will($this->returnValue($response));

        return $client;
    }

    protected function getCassetteMock($request, $response, $mode = DVD::MODE_NEW_EPISODES, $isNew = false)
    {
        $cassette = $this->getMockBuilder(Cassette::class)
            ->disableOriginalConstructor()
            ->setMethods(array('record', 'playback', 'isNew'))
            ->getMock();
        $cassette
            ->expects($this->once())
            ->method('playback')
            ->with($request)
            ->will($this->returnValue(null));

        if (DVD::MODE_NEW_EPISODES === $mode || DVD::MODE_ONCE === $mode && $isNew === true) {
            $cassette
                ->expects($this->once())
                ->method('record')
                ->with($request, $response);
        }

        if ($mode == 'once') {
            $cassette
                ->expects($this->once())
                ->method('isNew')
                ->will($this->returnValue($isNew));
        }

        return $cassette;
    }
}
