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
        $xml = $pearChannel->fetchPackagesXml();

        /** store packagesXml into channel rest root */
        Utils::mkpath( $root . DIRECTORY_SEPARATOR . 'rest' . DIRECTORY_SEPARATOR . 'p' );

        file_put_contents( 
            $root. DIRECTORY_SEPARATOR . 'rest' . DIRECTORY_SEPARATOR . 'p' . DIRECTORY_SEPARATOR . 'packages.xml',
            $dom->saveXML());

        

        /**
         * Print suggested Apache configuration for this 
         */



        $logger->info('Done');
    }

}





