<?php
namespace MiniPear\Progress;
use Phar;
use DOMDocument;
use DOMText;
use Exception;


/**
 *
 * MiniPear\Progress\UpdatePackage::setChannel( 'path/to/packageFile.tar', 'target.channel.net' );
 */
class UpdatePackage
{


    static function setChannel($packageFile,$channel)
    {

        $pharName = $packageFile . '.phar';

        rename( $packageFile , $pharName );

        /* try to read package file */
        try {
            // load package.xml
            $xml = file_get_contents( $p['package.xml'] );


            // patch package.xml
            $dom = new DOMDocument('1.0');
            $dom->loadXml( $xml );

            // get channel tag
            $channelNode = $dom->getElementsByTagName('channel')->item(0);

            // original channel
            $origChannel = $channelNode->nodeValue;

            $channelNode->removeChild( $channelNode->firstChild );
            $channelNode->appendChild( new DOMText($channel) );
            $xml = $dom->saveXml();

            // save package.xml
            $p['package.xml'] = $xml;
            
        } catch (Exception $e) {
             echo 'Could not modify file.txt:', $e->getMessage();
        }

        /* rename it back */
        rename( $pharName , $packageFile );
    }
}


