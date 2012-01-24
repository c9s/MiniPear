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

/** print progressbar **/
function curl_progressbar_cb($downloadSize, $downloaded, $uploadSize, $uploaded)
{
    // print progress bar
    $percent = ($downloaded > 0 ? (float) ($downloaded / $downloadSize) : 0.0 );
    $terminalWidth = 70;
    $sharps = (int) $terminalWidth * $percent;

    # echo "\n" . $sharps. "\n";
    print "\r" . 
        str_repeat( '#' , $sharps ) . 
        str_repeat( ' ' , $terminalWidth - $sharps ) . 
        sprintf( ' %4d B %5d%%' , $downloaded , $percent * 100 );

    if( $downloadSize === $downloaded )
        print "\n";
}


class CurlDownloader 
{
    public $progress = false;

    function fetch($url)
    {
        $options = array();
        $defaults = array( 
            CURLOPT_HEADER => 0, 
            CURLOPT_URL => $url, 
            CURLOPT_FRESH_CONNECT => 1, 
            CURLOPT_RETURNTRANSFER => 1, 
            CURLOPT_FORBID_REUSE => 1, 
            CURLOPT_TIMEOUT => 10, 
        ); 
        $ch = curl_init(); 
        curl_setopt_array($ch, ($options + $defaults)); 

        /* use progress */
        if( $this->progress ) {
            curl_setopt($ch, CURLOPT_NOPROGRESS, false);
            curl_setopt($ch, CURLOPT_PROGRESSFUNCTION, 'curl_progressbar_cb');
            curl_setopt($ch, CURLOPT_BUFFERSIZE, 128 );
        }

        if( ! $result = curl_exec($ch)) { 
            throw new Exception( $url . ":" . curl_error($ch) );
        }
        curl_close($ch); 
        return $result;
    }
}
