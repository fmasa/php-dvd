<?php

namespace DVD;

use DVD\LibraryHooks\CurlHook;
use DVD\LibraryHooks\SoapHook;
use DVD\LibraryHooks\StreamWrapperHook;
use DVD\Storage\Json;
use DVD\Storage\Yaml;
use DVD\Util\HttpClient;
use org\bovigo\vfs\vfsStream;

/**
 * Test instance creation.
 */
class DVDFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider instanceProvider
     */
    public function testCreateInstances($instance)
    {
        $this->assertInstanceOf($instance, DVDFactory::get($instance));
    }

    public function instanceProvider()
    {
        return array(
            array(Videorecorder::class),
            array(Configuration::class),
            array(HttpClient::class),
            array(CurlHook::class),
            array(SoapHook::class),
            array(StreamWrapperHook::class),
        );
    }

    /**
     * @dataProvider storageProvider
     */
    public function testCreateStorage($storage, $className)
    {
        vfsStream::setup('test');

        DVDFactory::get(Configuration::class)->setStorage($storage);
        DVDFactory::get(Configuration::class)->setCassettePath(vfsStream::url('test/'));

        $instance = DVDFactory::get('Storage', array(rand()));

        $this->assertInstanceOf($className, $instance);
    }

    public function storageProvider()
    {
        return array(
            array('json', Json::class),
            array('yaml', Yaml::class),
        );
    }
}
