<?php
namespace MiniPear;
use DOMDocument;
use Exception;
use MiniPear\CurlDownloader;

class PearChannel
{
    public $logger;
    public $downloader;

    public $host;
    public $alias;
    public $name;

    public $channelRestType;

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

        // $progress = new CurlProgressStar;
        // $this->downloader->progress = $progress;

        // load channel.xml
    }


    public function requestXml($url)
    {
        return $this->downloader->fetchXml( $url );
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
        if( ! $xml )
            die("channel.xml load failed.");

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
        $restNode = $primaryServerNode
                    ->getElementsByTagName('rest')->item(0);
        $baseUrlList = $restNode->getElementsByTagName('baseurl'); // get lastChild

        $baseUrl = $baseUrlList->item( $baseUrlList->length - 1 );
        $restType = $baseUrl->getAttribute('type');

        $this->channelRestBaseUrl = rtrim($baseUrl->nodeValue,'/');  # this should be read from channel.xml
        $this->channelRestType = $restType;


        $this->logger->info("REST type: $restType");


        /* rest schema urls */
        $this->packagesXmlUrl = $this->channelRestBaseUrl . '/p/packages.xml';
        $this->categoriesXmlUrl = $this->channelRestBaseUrl . '/c/categories.xml';
        $this->allMaintainersXmlUrl = $this->channelRestBaseUrl . '/m/allmaintainers.xml';

        /* save alias */
        $node = $xml->getElementsByTagName('suggestedalias')->item(0);
        $this->alias = $node->firstChild->nodeValue;

        /* save host name */
        $this->name = $xml->getElementsByTagName('name')->item(0)->nodeValue;


    }


    /**
     * fetch rest/p/packages.xml
     */
    public function fetchPackagesXml()
    {
        $xml = $this->packagesXml = $this->requestXml($this->packagesXmlUrl);
        if( ! $xml )
            die( $this->packagesXmlUrl . ' not found.');

        // $packagesXml->getElementsByTagName('c')->item(0);
        foreach( $this->packagesXml->getElementsByTagName('p') as $p ) {

        }
        return $xml;
    }


}



