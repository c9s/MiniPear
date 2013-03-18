<?php

use MiniPear\Progress\UpdatePackage;
require 'Archive/Tar.php';
class MiniPear_Progress_UpdatePackageTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var string
     */
    protected $packagePath;

    protected function preparePackage($testArchive = 'package.tgz')
    {
        $this->packagePath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $testArchive;
        copy(__DIR__ . DIRECTORY_SEPARATOR . '_files' . DIRECTORY_SEPARATOR . $testArchive, $this->packagePath);
    }

    protected function tearDown()
    {
        unlink($this->packagePath);
    }

    public function testSetChannelChangeTheChannelInPackageXml()
    {
        $this->preparePackage();
        $channel = 'new.channel.net';
        UpdatePackage::setChannel($this->packagePath, $channel);
        $tar = new \Archive_Tar($this->packagePath);
        $this->assertContains($channel, $tar->extractInString('package.xml'));
    }

    public function testSetChannelChangeTheChannelInPackage2Xml()
    {
        $this->preparePackage('package2.tgz');
        $channel = 'new.channel.net';
        UpdatePackage::setChannel($this->packagePath, $channel);
        $tar = new \Archive_Tar($this->packagePath);
        $this->assertContains($channel, $tar->extractInString('package2.xml'));
    }
}