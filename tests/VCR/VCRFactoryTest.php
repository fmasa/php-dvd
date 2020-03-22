<?php

namespace VCR;

use org\bovigo\vfs\vfsStream;

/**
 * Test instance creation.
 */
class VCRFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider instanceProvider
     * @covers VCR\VCRFactory::createVCRVideorecorder()
     */
    public function testCreateInstances($instance)
    {
        $this->assertInstanceOf($instance, VCRFactory::get($instance));
    }

    public function instanceProvider()
    {
        return array(
            array('VCR\Videorecorder'),
            array('VCR\Configuration'),
            array('VCR\Util\HttpClient'),
            array('VCR\LibraryHooks\CurlHook'),
            array('VCR\LibraryHooks\SoapHook'),
            array('VCR\LibraryHooks\StreamWrapperHook'),
        );
    }

    /**
     * @dataProvider storageProvider
     */
    public function testCreateStorage($storage, $className)
    {
        vfsStream::setup('test');

        VCRFactory::get('VCR\Configuration')->setStorage($storage);
        VCRFactory::get('VCR\Configuration')->setCassettePath(vfsStream::url('test/'));

        $instance = VCRFactory::get('Storage', array(rand()));

        $this->assertInstanceOf($className, $instance);
    }

    public function storageProvider()
    {
        return array(
            array('json', 'VCR\Storage\Json'),
            array('yaml', 'VCR\Storage\Yaml'),
        );
    }
}
