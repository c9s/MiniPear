<?php
namespace MiniPear;

class Config
{
    public $miniPearHome;
    public $miniPearChannelDir;
    public $config;

    function __construct()
    {
        /* read minipear config */
        $this->miniPearHome = getenv('HOME') . DIRECTORY_SEPARATOR . '.minipear';
        $this->miniPearChannelDir = $this->miniPearHome . DIRECTORY_SEPARATOR . 'pear'. DIRECTORY_SEPARATOR .'channels';
        if( ! file_exists( $this->miniPearHome ) )
            mkdir( $this->miniPearHome , 755 , true );
        if( ! file_exists( $this->miniPearChannelDir ) )
            mkdir( $this->miniPearChannelDir , 755 , true );

        $configFile = $this->miniPearHome . DIRECTORY_SEPARATOR . 'minipear.ini';

        /* default minipear config */
        $this->config = array(
       
        );
        if( file_exists($configFile) ) {
            $this->config = parse_ini_file( $configFile, true );
        }

    }

    public function getChannelRoot($host)
    {
        $localChannelRoot = $this->miniPearChannelDir . DIRECTORY_SEPARATOR . $host;
        if( ! file_exists($localChannelRoot) )
            mkdir( $localChannelRoot, 0755, true );
        return $localChannelRoot;
    }

    static function getInstance()
    {
        static $self;
        if( $self )
            return;
        return $self = new self;
    }
}





