<?php
namespace MiniPear;
use DOMDocument;
use MiniPear\CurlDownloader;

class PearChannel
{
    public $logger;
    public $downloader;

    public $host;
    public $channelXmlUrl;
    public $channelBaseUrl;


    /* dom xml objects */
    public $channelXml;

    public function __construct($host)
    {
        $this->host = $host;

        /* xxx: detect for http or https */
        $this->channelBaseUrl = 'http://' . $host;
        $this->channelXmlUrl = $this->channelBaseUrl . '/channel.xml';

        $this->downloader = new CurlDownloader;
    }

    public function requestXml($url)
    {
        $this->logger->info( "Fetching $url ..." );
        $xmlContent = $this->downloader->fetch( $url );

        /* load xml with DOMDocument */
        $dom = new \DOMDocument('1.0');
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = true;
        $dom->loadXML($xmlContent);
        return $dom;
    }

    public function fetchChannelXml()
    {
        return $this->channelXml = $this->requestXml( $this->channelXmlUrl );
    }

    /**
     * mirror to 
     *
     * @param string $localChannelRoot local channel root path.
     */
    public function mirror( $localChannelRoot )
    {
        $logger = $this->logger;

        /**
         * Get channel.xml from host
         *
         * xxx: support https and authentication ?
         *
         * @see http://pear.php.net/manual/en/guide.migrating.channels.xml.php
         * */
        $logger->info("Channel root: $localChannelRoot" );

        $channelBaseUrl = $this->channelBaseUrl;
        $channelXmlUrl = $this->channelXmlUrl;

        $dom = $this->fetchChannelXml();

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

        /* get rest base url */
        $restNode = $primaryServerNode->getElementsByTagName('rest')->item(0)->firstChild;
        $channelRestBaseUrl = rtrim($restNode->nodeValue,'/');  # this should be read from channel.xml

        /* rest schema urls */
        $packagesXmlUrl = $channelRestBaseUrl . '/p/packages.xml';
        $categoriesXmlUrl = $channelRestBaseUrl . '/c/categories.xml';
        $allmaintainersXmlUrl = $channelRestBaseUrl . '/m/allmaintainers.xml';


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
        $localHostname = $alias;
        $node->removeChild($node->firstChild);
        $node->appendChild(new \DOMText( $localHostname ));
        $logger->info("Hostname => $localHostname");



        /* save xml document */
        $xmlContent = $dom->saveXML();
        $channelXmlPath = $localChannelRoot . DIRECTORY_SEPARATOR . 'channel.xml';
        $logger->debug('Saving ' . $channelXmlPath );
        file_put_contents( $channelXmlPath, $xmlContent );


        /**
         * Mirror REST-ful part
         * @see http://pear.php.net/manual/en/core.rest.php
         */

        /** get packages */
        $packagesXmlContent = $this->downloader->fetch($packagesXmlUrl);
        $packagesXml = new DOMDocument;
        $packagesXml->loadXML( $packagesXmlContent );
        // $packagesXml->getElementsByTagName('c')->item(0);
        foreach( $packagesXml->getElementsByTagName('p') as $p ) {
            $logger->info( (string) $p->nodeValue );
        }
        $logger->info('Done');
        


        /**
         * Show suggested Apache configuration for this 
         */

    }

}



