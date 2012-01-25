<?php
namespace MiniPear\Command;
// use SimpleXMLElement;
use DOMDocument;
use MiniPear\CurlDownloader;
use MiniPear\Utils;


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

        Utils::$logger = $logger;

        /* read minipear config */
        $config = \MiniPear\Config::getInstance();
        $root = $config->getChannelRoot($host);


        $pearChannel = new \MiniPear\PearChannel( $host );
        $pearChannel->logger = $logger;

        /**
         * Get channel.xml from host
         *
         * xxx: support https and authentication ?
         *
         * @see http://pear.php.net/manual/en/guide.migrating.channels.xml.php
         * */
        $logger->info("Channel root: $root" );


        /**
         * xxx: ask people which server to mirror ?
         */
        $pearChannel->loadChannelXml();

        /**
         alter the channel alias with suffix -local 

         Note that "PEAR" runs `validate` on ChannelFile class, 
         domain names like: `pear.php.net`,`php-dev` is valid,
         domain names like: `pear_local.dev` is invalid.

         <channel ...>
            <name>pear.php.net</name>
            <suggestedalias>pear</suggestedalias>
            <summary>PHP Extension and Application Repository</summary>
            <servers> ... </servers>
         
         */
        {
            $alias = $pearChannel->alias;
            $alias = $alias . '-local';

            $dom = $pearChannel->channelXml;
            $node = $dom->getElementsByTagName('suggestedalias')->item(0);
            $node->removeChild($node->firstChild);
            $node->appendChild(new \DOMText( $alias ));
            // $logger->info("Alias => $alias");

            /**
             * alter the channel host to {{alias}}.dev 
             *
             *     alias pear => host pear-local.dev
             */
            $node = $dom->getElementsByTagName('name')->item(0);
            $localHostname = $alias;
            $node->removeChild($node->firstChild);
            $node->appendChild(new \DOMText( $localHostname ));


            /**
             * XXX: replace rest url with local alias and local host
             */
            $nodes = $dom->getElementsByTagName('primary')->item(0)->getElementsByTagName('baseurl');
            foreach( $nodes as $n ) {
                $url = $n->nodeValue;
                $info = parse_url( $url );
                $url = $info['scheme'] . '://' . $localHostname . $info['path'];
                $n->removeChild($n->firstChild);
                $n->appendChild(new \DOMText( $url ));
            }

            // $logger->info("Hostname => $localHostname");

            /* save xml document */
            $xmlContent = $dom->saveXML();
            $channelXmlPath = $root . DIRECTORY_SEPARATOR . 'channel.xml';
            $logger->debug('Saving ' . $channelXmlPath );
            file_put_contents( $channelXmlPath, $xmlContent );
        };


        /**
         * Mirror REST-ful part
         * @see http://pear.php.net/manual/en/core.rest.php
         */

        /** get packages */
        Utils::mirror_file( $pearChannel->packagesXmlUrl , $root );


        $logger->info('Getting package list...');
        $packageList = array();
        $packagesXml = $pearChannel->fetchPackagesXml();
        foreach( $packagesXml->getElementsByTagName('p') as $p ) {
            $packageList[] = $p->nodeValue;
        }


        /**
         * download package for /rest/p
         *
         *    info.xml
         *    maintainers.xml
         *    maintainers2.xml
         *
         */
        $logger->info('Mirroring package info section...');
        foreach( $packageList as $packageName ) {
            $urls = array();
            $urls[] = $pearChannel->channelRestBaseUrl . '/p/' . strtolower($packageName) . '/info.xml';
            $urls[] = $pearChannel->channelRestBaseUrl . '/p/' . strtolower($packageName) . '/maintainers.xml';
            $urls[] = $pearChannel->channelRestBaseUrl . '/p/' . strtolower($packageName) . '/maintainers2.xml';
            foreach( $urls as $url ) {
                Utils::mirror_file( $url , $root );
            }
        }

        /** 
         * download packages for /rest/r
         * release info:
         *
         *  - allrelease.xml
         *  - allrelease2.xml
         *  - latest.txt
         *  - stable.txt
         *  - beta.txt
         *  - alpha.txt
         *  - devel.txt
         *
         *  - {version}.xml
         *  - v2.{version}.xml
         *  - package.{version}.xml
         *  - deps.{version}.txt
         */
        $logger->info('Mirroring package info section...');
        foreach( $packageList as $packageName ) {
            $base = $pearChannel->channelRestBaseUrl . '/r/' . strtolower($packageName); // . '/info.xml';

            $stabilities = array();
            $versions = array();

            // parse allreleases.xml for package versions
            $xml = $pearChannel->requestXml( $base . '/allreleases2.xml' );
            $nodes = $xml->getElementsByTagName('r');
            foreach( $nodes as $n ) {
                $version = $n->getElementsByTagName('v')->item(0)->nodeValue;
                $stability = $n->getElementsByTagName('s')->item(0)->nodeValue;
                $phpVersion = $n->getElementsByTagName('m')->item(0)->nodeValue; // minimal php version
                $versions[] = $version;
                $stabilities[ $stability ] = 1;
            }


            $files = array();
            $files[] = 'allreleases.xml';
            $files[] = 'allreleases2.xml';

            $files[] = 'latest.txt';
            foreach( array_keys($stabilities) as $s ) {
                $files[] = $s . '.txt';
            }

            foreach( $versions as $version ) {
                $files[] = $version . '.txt';
                $files[] = 'v2.' . $version . '.xml';
                $files[] = 'package.' . $version . '.xml';
                $files[] = 'deps.' . $version . '.txt';
            }

            foreach( $files as $file ) {
                Utils::mirror_file(  $base . '/' . $file , $root );
            }
        }

        /**
         * Print suggested Apache configuration for this 
         */


        $logger->info('Done');
    }

}





