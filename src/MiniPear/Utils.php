<?php
namespace MiniPear;

class Utils
{
    static $logger;

    static function mkpath($path)
    {
        if( ! file_exists($path) )
            mkdir( $path , 0755 , true );
    }

    static function mirror_file($url,$root)
    {
        self::$logger->info( $url , 1 );

        $info = parse_url( $url );
        $dirname = dirname($info['path']);
        $filename = basename($info['path']);
        $localPath = $root . $dirname;
        self::mkpath( $localPath );
        $localFilePath = $localPath . DIRECTORY_SEPARATOR . $filename;
        if( file_exists($localFilePath) )
            return;

        $d = new CurlDownloader;
        $content = $d->fetch( $url );
        if( $content ) {
            if( file_put_contents( $localFilePath , $content ) !== false )
                return true;
        }
        return false;
    }

}
