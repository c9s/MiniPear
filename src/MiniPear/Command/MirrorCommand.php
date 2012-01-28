<?php
namespace MiniPear\Command;
// use SimpleXMLElement;
use DOMDocument;
use MiniPear\CurlDownloader;
use MiniPear\Utils;
use MiniPear\Progress\UpdatePackage;
use Exception;


class MirrorCommand extends \CLIFramework\Command
{

    public function brief()
    {
        return 'mirror a PEAR channel.';
    }


    public function options($opts)
    {
        $opts->add('c|channel?','local channel hostname');
        $opts->add('a|alias?',  'local channel alias');
    }

    public function execute($host)
    {
        $options = $this->getOptions();
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

        $localAlias = $pearChannel->alias;
        if( $options->alias ) {
            $localAlias = $options->alias->value;
        } else {
            $localAlias = $localAlias . '-local';
        }

        // use alias as local hostname by default.
        if( $options->channel ) {
            $localChannel = $options->channel->value;
        } else {
            $localChannel = $localAlias;
        }

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

            $dom = $pearChannel->channelXml;
            $node = $dom->getElementsByTagName('suggestedalias')->item(0);
            $node->removeChild($node->firstChild);
            $node->appendChild(new \DOMText( $localAlias ));
            // $logger->info("Alias => $localAlias");

            /**
             * alter the channel host to {{localAlias}}.dev 
             *
             *     alias pear => host pear-local.dev
             */
            $node = $dom->getElementsByTagName('name')->item(0);
            $node->removeChild($node->firstChild);
            $node->appendChild(new \DOMText( $localChannel ));


            /**
             * replace rest url with local alias and local host
             */
            $nodes = $dom->getElementsByTagName('primary')->item(0)->getElementsByTagName('baseurl');
            foreach( $nodes as $n ) {
                $url = $n->nodeValue;
                $info = parse_url( $url );
                $url = $info['scheme'] . '://' . $localChannel . $info['path'];
                $n->removeChild($n->firstChild);
                $n->appendChild(new \DOMText( $url ));
            }

            // $logger->info("Hostname => $localChannel");



            /* save xml document */
            $xmlContent = $dom->saveXML();

            // xxx: because of the stupid PEAR uses a stupid pcre pattern to 
            // validate channel version, we have to fix this by hands.
            // $xmlContent = str_replace('<channel ','<channel version="1.0" ', $xmlContent );
            $declare = '<channel version="1.0" 
                xmlns="http://pear.php.net/channel-1.0" 
                xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" 
                xsi:schemaLocation="http://pear.php.net/channel-1.0 
                http://pear.php.net/dtd/channel-1.0.xsd">';
            $xmlContent = preg_replace( '/<channel [^>]+>/' , $declare , $xmlContent );


            $channelXmlPath = $root . DIRECTORY_SEPARATOR . 'channel.xml';
            $logger->debug('Saving ' . $channelXmlPath );
            file_put_contents( $channelXmlPath, $xmlContent );
        };


        /**
         * Mirror REST-ful part
         * @see http://pear.php.net/manual/en/core.rest.php
         */

        /** get packages */
        if( $localFile = Utils::mirror_file( $pearChannel->packagesXmlUrl , $root ) ) {
            if( $sxml = Utils::load_xml_file( $localFile ) ) {
                $sxml->c = $localChannel;
                $sxml->asXML( $localFile );
            }
        }

        if( $localFile = Utils::mirror_file( $pearChannel->categoriesXmlUrl , $root ) ) { //  mirror /rest/c/categories.xml
            if( $sxml = Utils::load_xml_file( $localFile ) ) {
                $sxml->ch = $localChannel;
                $sxml->asXML( $localFile );
            }
        }


        /** xxx: mirror categories **/


        /** get package list **/
        $logger->info('Getting package list...');
        $packageList = array();
        $packagesXml = $pearChannel->fetchPackagesXml();
        foreach( $packagesXml->getElementsByTagName('p') as $p ) {
            $packageList[] = $p->nodeValue;
        }

        $logger->info( count($packageList) . ' packages to mirror.' );


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

            if( version_compare($pearChannel->restType,'REST1.2') >= 0 ) {
                $urls[] = $pearChannel->channelRestBaseUrl . '/p/' . strtolower($packageName) . '/maintainers2.xml';
            }

            foreach( $urls as $url ) {
                if( $localFile = Utils::mirror_file( $url , $root ) ) {
                    if( $sxml = Utils::load_xml_file( $localFile ) ) {
                        $sxml->c = $localChannel;
                        $sxml->asXML( $localFile );
                    }
                }
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

        /**
         * @var array (
         *    'Package_Name' => [ versions ... ],
         * )
         */
        $packageVersions = array();

        $logger->info('Mirroring package info section...');
        foreach( $packageList as $packageName ) {
            $base = $pearChannel->channelRestBaseUrl . '/r/' . strtolower($packageName); // . '/info.xml';

            $stabilities = array();
            $versions = array();

            $xml = null;

            try {
                // parse allreleases.xml for package versions

                if( version_compare($pearChannel->restType,'REST1.3') >= 0 ) {
                    $xml = $pearChannel->requestXml( $base . '/allreleases2.xml' );
                }
                else {
                    $xml = $pearChannel->requestXml( $base . '/allreleases.xml' );
                }

                if( ! $xml ) {
                    throw new Exception("$base/allreleases.xml fetch failed.");
                }

            } catch( Exception $e ) {
                $logger->error( $e->getMessage() );
                continue;
            }

            $nodes = $xml->getElementsByTagName('r');
            foreach( $nodes as $n ) {
                $version = $n->getElementsByTagName('v')->item(0)->nodeValue;
                $stability = $n->getElementsByTagName('s')->item(0)->nodeValue;
                // $phpVersion = $n->getElementsByTagName('m')->item(0)->nodeValue; // minimal php version
                $versions[] = $version;
                if( in_array( $stability, array('alpha','beta','stable') ) )  {
                    $stabilities[ $stability ] = 1;
                }
            }

            $files = array();

            $stabilityVersions = array();

            if( $localFile = Utils::mirror_file(  $base . '/latest.txt' , $root ) ) {
                $stabilityVersions['latest'] = file_get_contents( $localFile );
            }

            foreach( array_keys($stabilities) as $s ) {
                if( $localFile = Utils::mirror_file(  $base . '/' . $s . '.txt' , $root ) ) {
                    $stabilityVersions[ $s ] = file_get_contents( $localFile );
                }
            }

            // xxx: do not mirror all version 
            $versions = array_values( $stabilityVersions );

            foreach( $versions as $version ) {
                Utils::mirror_file( $base . '/deps.' . $version . '.txt', $root );

                if( $localFile = Utils::mirror_file( $base . '/' . $version . '.xml' , $root ) ) {
                    if( $sxml = Utils::load_xml_file( $localFile ) ) {
                        $sxml->c = $localChannel;
                        $sxml->g = str_replace( $pearChannel->name, $localChannel , (string) $sxml->g );
                        $sxml->asXML( $localFile );
                    }
                }



                if( $localFile = Utils::mirror_file( $base . '/package.' . $version . '.xml' , $root ) ) {
                    if( $sxml = Utils::load_xml_file( $localFile ) ) {
                        $sxml->channel = $localChannel;
                        $sxml->asXML( $localFile );
                    }
                }

                if( $localFile = Utils::mirror_file( $base . '/allreleases.xml', $root) ) {
                    if( $sxml = Utils::load_xml_file( $localFile ) ) {
                        $sxml->c = $localChannel;
                        $sxml->asXML( $localFile );
                    }
                }

                if( version_compare($pearChannel->restType,'REST1.3') >= 0 ) {

                    if( $localFile = Utils::mirror_file( $base . '/allreleases2.xml', $root) ) {
                        if( $sxml = Utils::load_xml_file( $localFile ) ) {
                            $sxml->c = $localChannel;
                            $sxml->asXML( $localFile );
                        }
                    }

                    if( $localFile = Utils::mirror_file( $base . '/v2.' . $version . '.xml', $root ) ) {
                        if( $sxml = Utils::load_xml_file( $localFile ) ) {
                            $sxml->c = $localChannel;
                            $sxml->g = str_replace( $pearChannel->name, $localChannel , (string) $sxml->g );
                            $sxml->asXML( $localFile );
                        }
                    }

                }

            }


            // save Package version
            $packageVersions[ $packageName ] = $versions;
        }


        /**
         * foreach package with different verions, 
         * download them all.
         */
        $logger->info("Downloading package files...");
        foreach( $packageVersions as $packageName => $versions ) {
            $base = $pearChannel->channelBaseUrl . '/get/';
            $formats = array('tar','tgz');
            $urls = array();
            foreach( $versions as $version ) {
                foreach( $formats as $format) {
                    $urls[] = $base . $packageName . '-' . $version . '.' . $format;
                }
            }

            foreach( $urls as $url ) {
                if( $file = Utils::mirror_file( $url, $root )  ) {
                    $logger->debug2("Update package channel to $localChannel",1);
                    UpdatePackage::setChannel( $file , $localChannel );
                }
            }
        }


        $logger->info('Done');

        /**
         * Print suggested Apache configuration for this 
         */
        $help =<<<EOS
You can now add the config below to setup the local pear channel server:

    <VirtualHost *:80>
        ServerName $localChannel
        DocumentRoot "$root"
    </VirtualHost>

And append this line to your /etc/hosts file:

    127.0.0.1 $localChannel

Run pear to discover your mirror:

    $ pear channel-discover $localChannel

EOS;
        echo $help;
    }

}





