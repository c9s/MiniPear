<?php
namespace MiniPear;
use DOMDocument;
use DOMText;
use SimpleXMLElement;

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

    static function patchPackageXmlFile($file, $toChannel)
    {
        $xml = self::patchPackageXml( file_get_contents($file), $toChannel );
        return file_put_contents( $file, $xml );
    }

    static function patchPackageXmlPackageDep($packageNode, $fromChannel, $toChannel) {
        if ($packageNode->getName() == "package" ) {
            if ( ((string)$packageNode->channel) == $fromChannel ) {
                $packageNode->channel = $toChannel;
            }
        }
    }


    static function patchPackageXml($xmlContent, $toChannel) 
    {
        if ( $xmlContent instanceof SimpleXmlElement ) {
            $sxml = $xmlContent;
        } else if ( file_exists($xmlContent) ) {
            $sxml = @simplexml_load_file( $file );
            if ( ! $sxml ) {
                return false;
            }
        } else if ( is_string($xmlContent) ) {
            $sxml = new SimpleXmlElement( $xmlContent );
        }
        $fromChannel = (string) $sxml->channel;
        $sxml->channel = $toChannel;

        if ( $deps = $sxml->dependencies ) {
            if ( $required = $deps->required ) {
                foreach ( $required->children() as $child ) {
                    self::patchPackageXmlPackageDep($child, $fromChannel, $toChannel);
                }
            }
            if ( $optional = $deps->optional ) {
                foreach ( $optional->children() as $child ) {
                    self::patchPackageXmlPackageDep($child, $fromChannel, $toChannel);
                }
            }
        }
        return $sxml->asXML();
    }

    static function patchDepDep($content, $fromChannel, $toChannel) {
        $data = unserialize($content);
        if ( isset($data['required']) && isset($data['required']['package']) ) {
            $packages = isset($data['required']['package'][0]) ? $data['required']['package'] : array($data['required']['package']);
            foreach( $packages as & $pkg ) {
                if ( isset($pkg['channel']) ) {
                    if ( $pkg['channel'] == $fromChannel ) {
                        $pkg['channel'] = $toChannel;
                    }
                }
            }
            $data['required']['package'] = $packages;
        }
        if ( isset($data['optional']) && isset($data['optional']['package']) ) {
            $packages = isset($data['optional']['package'][0]) ? $data['optional']['package'] : array($data['optional']['package']);
            foreach( $packages as & $pkg ) {
                if ( isset($pkg['channel']) ) {
                    if ( $pkg['channel'] == $fromChannel ) {
                        $pkg['channel'] = $toChannel;
                    }
                }
            }
            $data['optional']['package'] = $packages;
        }
        return serialize($data);
    }

    static function mirror_file($url,$root)
    {
        self::$logger->info2( $url , 1 );

        $info = parse_url( $url );
        $dirname = dirname($info['path']);
        $filename = basename($info['path']);
        $localPath = $root . $dirname;
        self::mkpath( $localPath );
        $localFilePath = $localPath . DIRECTORY_SEPARATOR . $filename;
        if( file_exists($localFilePath) )
            return $localFilePath;

        $d = new CurlDownloader;
        $progress = new CurlProgressStar;
        $d->progress = $progress;

        $content = $d->fetch( $url );
        if( $content == false ) {
            // self::$logger->warn( $url . ' failed.' );
            return false;
        }

        if( file_put_contents( $localFilePath , $content ) !== false )
            return $localFilePath;

        self::$logger->error( "File write failed: $localFilePath" );
        return false;
    }


    static function load_xml_file($file)
    {
        $sxml = @simplexml_load_file( $file );
        if ( $sxml ) {
            return $sxml;
        }
        self::$logger->error("xml file load failed.");
        return false;
    }

}
