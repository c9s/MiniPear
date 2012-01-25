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

    static function pretty_size($bytes)
    {
        if( $bytes > 1000000 ) {
            return (int)( $bytes / 1000000 ) . 'M';
        }
        elseif( $bytes > 1000 ) {
            return (int)( $bytes / 1000 ) . 'K';
        }
        return (int) ($bytes) . 'B';
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
        $progress = new CurlProgressStar;
        $d->progress = $progress;

        $content = $d->fetch( $url );
        if( ! $content ) {
            self::$logger->error( $url . ' failed.' );
            return false;
        }

        if( file_put_contents( $localFilePath , $content ) !== false )
            return true;

        self::$logger->error( "File write failed: $localFilePath" );
        return false;
    }

}
