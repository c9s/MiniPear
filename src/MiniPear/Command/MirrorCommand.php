<?php
namespace MiniPear\Command;
use SimpleXMLElement;
use MiniPear\CurlDownloader;

class MirrorCommand extends \CLIFramework\Command
{

    public function brief()
    {
        return 'mirror a PEAR channel.';
    }

    function execute($host)
    {
        $logger = $this->getLogger();
        $logger->info( "Starting mirror $host..." );

        /* read minipear config */
        $miniPearHome = getenv('HOME') . DIRECTORY_SEPARATOR . '.minipear';
        $miniPearChannelDir = $miniPearHome . DIRECTORY_SEPARATOR . 'pear'. DIRECTORY_SEPARATOR .'channels';
        if( ! file_exists( $miniPearHome ) )
            mkdir( $miniPearHome , 755 , true );
        if( ! file_exists( $miniPearChannelDir ) )
            mkdir( $miniPearChannelDir , 755 , true );

        $configFile = $miniPearHome . DIRECTORY_SEPARATOR . 'minipear.ini';

        /* default minipear config */
        $config = array(
       
        );
        if( file_exists($configFile) ) {
            $config = parse_ini_file( $configFile, true );
        }

        /* get channel.xml from host
         *
         * xxx: support https and authentication ?
         * */
        $channelXmlUrl = 'http://' . $host . '/channel.xml';
        $logger->info( "Fetching $channelXmlUrl ..." );
        $d = new CurlDownloader;
        $xmlContent = $d->fetch( $channelXmlUrl );

        /* load xml with DOMDocument */
        $dom = new \DOMDocument('1.0');
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = true;
        $dom->loadXML($xmlContent);


        /**
         * xxx: ask people which server to mirror ?
         */
        $serversNode = $dom->getElementsByTagName('servers')->item(0);
        $primaryServerNode = $serversNode->getElementsByTagName('primary')->item(0);
        $mirrorNodes = $serversNode->getElementsByTagName('mirror');
        $mirrors = array();

        foreach( $mirrorNodes as $mirrorNode ) {
            $mirrors[] = $mirrorNode;
            $mirrorHost = $mirrorNode->getAttribute('host');
            $logger->info( "=> Found mirror site: " . $mirrorHost , 1 );
        }


        /**
         alter the channel alias with suffix _local 

         <channel ...>
            <name>pear.php.net</name>
            <suggestedalias>pear</suggestedalias>
            <summary>PHP Extension and Application Repository</summary>
            <servers> ... </servers>
         
         */
        $nodes = $dom->getElementsByTagName('suggestedalias');
        $node = $nodes->item(0);

        $alias = $node->firstChild->nodeValue;
        $alias = $alias . '_local';

        $node->removeChild($node->firstChild);
        $node->appendChild(new \DOMText( $alias ));
        $xmlContent = $dom->saveXML();




    }

}





