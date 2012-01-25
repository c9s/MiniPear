<?php
namespace MiniPear\Command;
// use SimpleXMLElement;
use DOMDocument;
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
        $config = \MiniPear\Config::getInstance();
        $localChannelRoot = $config->getChannelRoot($host);


        $pearChannel = new \MiniPear\PearChannel( $host );
        $pearChannel->logger = $logger;
        $pearChannel->mirror( $localChannelRoot );

    }

}





