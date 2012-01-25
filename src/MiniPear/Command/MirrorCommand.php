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

        $localChannelRoot = $miniPearChannelDir . DIRECTORY_SEPARATOR . $host;
        if( ! file_exists($localChannelRoot) )
            mkdir( $localChannelRoot, 0755, true );


        $pearChannel = new \MiniPear\PearChannel( $host );
        $pearChannel->logger = $logger;
        $pearChannel->mirror( $localChannelRoot );

    }

}





