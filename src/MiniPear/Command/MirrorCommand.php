<?php
namespace MiniPear\Command;

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
        $d = new CurlDownloader;
        $xmlContent = $d->fetch( $channelXmlUrl );

        /* alter the channel alias with suffix _local */



    }

}





