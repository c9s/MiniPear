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

        /**
         * Get channel.xml from host
         *
         * xxx: support https and authentication ?
         *
         * @see http://pear.php.net/manual/en/guide.migrating.channels.xml.php
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
         alter the channel alias with suffix -local 

         Note that "PEAR" runs `validate` on ChannelFile class, 
         domain names like: `pear.php.net`,`php-dev` is valid,
         domain names like: `pear_local.dev` is invalid.

         <channel ...>
            <name>pear.php.net</name>
            <suggestedalias>pear</suggestedalias>
            <summary>PHP Extension and Application Repository</summary>
            <servers> ... </servers>
         
         */
        $node = $dom->getElementsByTagName('suggestedalias')->item(0);

        $alias = $node->firstChild->nodeValue;
        $alias = $alias . '-local';

        $node->removeChild($node->firstChild);
        $node->appendChild(new \DOMText( $alias ));
        $logger->info("Alias => $alias");

        /**
         * alter the channel host to {{alias}}.dev 
         *
         *     alias pear => host pear-local.dev
         */
        $node = $dom->getElementsByTagName('name')->item(0);
        $localHostname = $alias . '.dev';
        $node->removeChild($node->firstChild);
        $node->appendChild(new \DOMText( $localHostname ));
        $logger->info("Hostname => $localHostname");



        /* save xml document */
        $xmlContent = $dom->saveXML();


        /**
         * Mirror REST-ful part
         * @see http://pear.php.net/manual/en/core.rest.php
         */



        /**
         * Show suggested Apache configuration for this 
         */
    }

}





