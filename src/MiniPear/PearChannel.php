<?php
namespace MiniPear;
use DOMDocument;
use MiniPear\CurlDownloader;

class PearChannel
{
    public $logger;
    public $downloader;

    public $host;
    public $alias;
    public $name;

    /* url list */
    public $channelXmlUrl;
    public $channelBaseUrl;
    public $channelRestBaseUrl;
    public $packagesXmlUrl;
    public $categoriesXmlUrl;
    public $allMaintainersXmlUrl;


    /* dom xml objects */
    public $channelXml;
    public $packagesXml;

    public function __construct($host)
    {
        $this->host = $host;

        /* xxx: detect for http or https */
        $this->channelBaseUrl = 'http://' . $host;
        $this->channelXmlUrl = $this->channelBaseUrl . '/channel.xml';
        $this->downloader = new CurlDownloader;

        // load channel.xml
    }


    public function requestXml($url)
    {
        // $this->logger->info( "Fetching $url ..." );
        $xmlContent = $this->downloader->fetch( $url );

        /* load xml with DOMDocument */
        $dom = new DOMDocument('1.0');
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = true;
        $dom->loadXML($xmlContent);
        return $dom;
    }

    public function fetchChannelXml()
    {
        return $this->channelXml = $this->requestXml( $this->channelXmlUrl );
    }

    public function loadChannelXml()
    {
        // $this->channelBaseUrl;
        // $this->channelXmlUrl;
        $xml = $this->fetchChannelXml();

        $serversNode = $xml->getElementsByTagName('servers')->item(0);
        $primaryServerNode = $serversNode->getElementsByTagName('primary')->item(0);
        $mirrorNodes = $serversNode->getElementsByTagName('mirror');

        // xxx: save mirrors
        $mirrors = array();
        foreach( $mirrorNodes as $mirrorNode ) {
            $mirrors[] = $mirrorNode;
            $mirrorHost = $mirrorNode->getAttribute('host');
        }

        /* get rest base url */
        $restNode = $primaryServerNode->getElementsByTagName('rest')->item(0)->firstChild;
        $this->channelRestBaseUrl = rtrim($restNode->nodeValue,'/');  # this should be read from channel.xml


        /* rest schema urls */
        $this->packagesXmlUrl = $this->channelRestBaseUrl . '/p/packages.xml';
        $this->categoriesXmlUrl = $this->channelRestBaseUrl . '/c/categories.xml';
        $this->allMaintainersXmlUrl = $this->channelRestBaseUrl . '/m/allmaintainers.xml';

        /* save alias */
        $node = $xml->getElementsByTagName('suggestedalias')->item(0);
        $this->alias = $node->firstChild->nodeValue;

        /* save host name */
        $this->name = $xml->getElementsByTagName('name')->item(0);


    }


    /**
     * fetch rest/p/packages.xml
     */
    public function fetchPackagesXml()
    {
        $xml = $this->packagesXml = $this->requestXml($this->packagesXmlUrl);

        // $packagesXml->getElementsByTagName('c')->item(0);
        foreach( $this->packagesXml->getElementsByTagName('p') as $p ) {

        }
        return $xml;
    }


}



