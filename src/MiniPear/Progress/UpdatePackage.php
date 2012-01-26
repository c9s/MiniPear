<?php
namespace MiniPear\Progress;
use Phar;
use DOMDocument;
use DOMText;
use Exception;
use PharData;


/**
 *
 * MiniPear\Progress\UpdatePackage::setChannel( 'path/to/packageFile.tar', 'target.channel.net' );
 */
class UpdatePackage
{


    static function setChannel($packageFile,$channel)
    {

        /*
         * xxx: phar doesn't support tgz format.
         */
        try {
        $p = new PharData($packageFile, 0,'phartest.phar'); 

        # $pharName = str_replace('.tgz','.phar',$packageFile);
        # rename( $packageFile , $pharName );
        # $pharUri = 'phar://' . $pharName . '/package.xml';

        // load package.xml
        $xml = file_get_contents( $p['package.xml'] );
        $sxml = new \SimpleXmlElement( $xml );
        $sxml->channel = $channel;
        $xml = $sxml->asXML();

        // $xml = \MiniPear\Utils::change_package_xml_channel( $xml , $channel );

        // save package.xml
        $p['package.xml'] = $xml;
        // file_put_contents( $pharUri , $xml );

        /* rename it back */
        // rename( $pharName , $packageFile );
        } catch( Exception $e ) {
            die( $e->getMessage() );
        }
    }

}


