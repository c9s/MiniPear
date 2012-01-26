MiniPear - PEAR Channel Local Mirror Tool
=========================================
MiniPear can create local pear channel mirrors for offline usage.

To create a mirror:

    $ minipear mirror {channel host}

Print verbose / debug messages:

    $ minipear -d mirror {channel host}

Channel sites will be mirrored into ~/.minipear/pear/channels.

The channel host will be replaced by `{alias}-local`, you can install packages
from these local pear hosts when you are offline.

For example:

    $ sudo pear channel-discover pear-local
    $ sudo pear install pear-local/Archive_Tar

## Author 

Yo-An Lin <cornelius.howl@gmail.com>
