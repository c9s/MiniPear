<?php
namespace MiniPear;

class ChannelListStore
{

    /**
     * {identity: remote pear channel host} => {
     *     type  => 'pear',      // pear channel
     *     alias => 'pear.dev',  // pear channel alias
     *     host  => '....',      // pear channel host (or mirror from)
     * }
     *
     * Pear Channel:
     *
     * pear.php.net => {
     *       type => 'pear',
     *       remote_host  => 'pear.php.net',
     *       remote_alias => 'pear',
     *       local_alias => 'pear-local',
     *       local_host  => 'pear-local.dev',
     *    }
     *
     * MiniPear Channel:
     *
     *
     *
     */
    public $channels = array();

    function __construct()
    {
        $this->load();
    }

    function getChannelListFile()
    {
        // load from minipear home
        $config = MiniPear\Config::getInstance();
        $channeListJsonFile = $config->miniPearChannelDir . DIRECTORY_SEPARATOR . 'channels.json';
        return $channeListJsonFile;
    }

    public function has($host)
    {
        return isset( $this->channels[ $host ] );
    }

    public function addPearChannel($channel)
    {
        // $this->channels[ $channel ]
    }

    public function load()
    {
        $channelListJsonFile = $this->getChannelListFile();
        if( file_exists( $channelListJsonFile ) ) 
            $this->channels = json_decode( $channelListJsonFile );
    }

    public function save()
    {
        $channelListJsonFile = $this->getChannelListFile();
        file_put_contents( $channelListJsonFile , json_encode( $this->channels ));
    }

}







