<?php

class UtilsTest extends PHPUnit_Framework_TestCase
{


    function testPatchDepDep()
    {
        $content = file_get_contents('http://pear.php.net/rest/r/text_captcha/deps.0.5.0.txt');
        $content = \MiniPear\Utils::patchDepDep($content, 'pear.php.net', 'pear-local');
        ok($content);
        // var_dump( unserialize($content) ); 
    }

    function test()
    {

        \MiniPear\Utils::$logger = \CLIFramework\Logger::getInstance();
        \MiniPear\Utils::mirror_file('http://pear.php.net/channel.xml','test');
        path_ok('test/channel.xml');
        unlink('test/channel.xml');

        \MiniPear\Utils::mirror_file('http://pear.php.net/rest/p/packages.xml','test');
        path_ok('test/rest/p/packages.xml');
        unlink('test/rest/p/packages.xml');

        // var_dump( version_compare( 'REST1.1' , 'REST1.2' ) );
        // var_dump( version_compare( 'REST1.1' , 'REST1.1' ) );
        // var_dump( version_compare( 'REST1.1' , 'REST1.0' ) );
    }
}


