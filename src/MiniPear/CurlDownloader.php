<?php
namespace MiniPear;

/**
 *
 *  $d = new CurlDownlaoder;
 *  $d->progress = true;
 *  $content = $d->fetch( $url );
 *  $d->fetchAsFile( $url , $as );
 *
 * xxx: support authentication for curl
 * http://www.php.net/manual/en/function.curl-setopt.php
 */
use Exception;

class CurlProgressStar
{
    public $stars = array('-','\\','|','/');
    public $i = 0;
    public $url;
    public $done = false;

    public function progress($downloadSize, $downloaded, $uploadSize, $uploaded)
    {
        /* 4kb */
        if( $downloadSize < 8000 ) {
            return;
        }
        if( $this->done ) {
            return;
        }

        // printf("%s % 4d%%", $s , $percent );

        if( $downloadSize != 0 && $downloadSize === $downloaded ) {
            $this->done = true;
            printf("\r\t%-60s                         \n",$this->url);
        } else {
            $percent = ($downloaded > 0 ? (float) ($downloaded / $downloadSize) : 0.0 );
            if( ++$this->i > 3 )
                $this->i = 0;

            /* 8 + 1 + 60 + 1 + 1 + 1 + 6 = */
            printf("\r\tFetching %-60s %s % 3.1f%% %s", $this->url,
                $this->stars[ $this->i ], 
                $percent * 100, Utils::pretty_size($downloaded) );
        }
    }
}

class CurlProgressBar 
{
    public $done = false;
    public $url;

    /** print progressbar **/
    public function progress($downloadSize, $downloaded, $uploadSize, $uploaded)
    {
        if( $this->done )
            return;

        // print progress bar
        $percent = ($downloaded > 0 ? (float) ($downloaded / $downloadSize) : 0.0 );
        $terminalWidth = 70;
        $sharps = (int) $terminalWidth * $percent;

        # echo "\n" . $sharps. "\n";
        print "\r" . 
            str_repeat( '#' , $sharps ) . 
            str_repeat( ' ' , $terminalWidth - $sharps ) . 
            sprintf( ' %4d B %5d%%' , $downloaded , $percent * 100 );

        if( $downloadSize != 0 && $downloadSize === $downloaded ) {
            $this->done = true;
            print "\n";
        }
    }
}



class CurlDownloader 
{
    public $progress = false;
    public $timeout = 10;

    public function fetchXml($url)
    {
        // $this->logger->info( "Fetching $url ..." );
        $xmlContent = $this->fetch( $url );

        if( strpos($xmlContent,'<?xml') === false )
            throw new Exception( "$url is not an XML format content." );

        /* load xml with DOMDocument */
        $dom = new \DOMDocument('1.0');
        
        $dom->preserveWhiteSpace = true;
        $dom->formatOutput = true;

        if( @$dom->loadXML($xmlContent) === false )
            throw new Exception( 'XML error: ' . $url  ); 
        return $dom;
    }

    public function fetch($url)
    {
        $options = array();
        $defaults = array( 
            CURLOPT_HEADER => 0, 
            CURLOPT_URL => $url, 
            CURLOPT_FRESH_CONNECT => 1, 
            CURLOPT_RETURNTRANSFER => 1, 
            CURLOPT_FORBID_REUSE => 1, 
            CURLOPT_TIMEOUT => $this->timeout, 
        ); 
        $ch = curl_init(); 
        curl_setopt_array($ch, ($options + $defaults)); 

        /* use progress */
        if( $this->progress ) {
            if( $this->progress === true ) {
                $this->progress = new CurlProgressBar;
            }
            $this->progress->url = $url;
            curl_setopt($ch, CURLOPT_NOPROGRESS, false);
            curl_setopt($ch, CURLOPT_PROGRESSFUNCTION, array($this->progress,'progress') );
            curl_setopt($ch, CURLOPT_BUFFERSIZE, 64 );
        }

        /*
        if( ! $result = curl_exec($ch))
            return false;
        if(curl_getinfo($ch, CURLINFO_HTTP_CODE) !== 400 )
            return false;
         */

        if( ! $result = curl_exec($ch))
            throw new Exception( 'Curl Error: ' . $url . " - " . curl_error($ch) );
        if( curl_getinfo($ch, CURLINFO_HTTP_CODE) === 400 )
            throw new Exception( "Curl Error: 404 Not Found." );

        curl_close($ch); 
        return $result;
    }
}
