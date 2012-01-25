<?php
namespace MiniPear;

class Utils
{
    static function mkpath($path)
    {
        if( ! file_exists($path) )
            mkdir( $path , 0755 , true );
    }

    static function mirror_file($url,$root)
    {
        $info = parse_url( $url );
        $dirname = dirname($info['path']);
        $filename = basename($info['path']);
        $localPath = $root . $dirname;
        self::mkpath( $localPath );

        $d = new CurlDownloader;
        $content = $d->fetch( $url );
        if( $content ) {
            if( file_put_contents( $localPath . DIRECTORY_SEPARATOR . $filename , $content ) !== false )
                return true;
        }
        return false;
    }

}
