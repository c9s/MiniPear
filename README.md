MiniPear - PEAR Channel Mirror Tool
===================================
MiniPear can create local pear channel mirror for offline usage.

To create a mirror:

    $ minipear mirror {channel host}

Channel sites will be mirrored into ~/.minipear/pear/channels.

The channel host will be replaced by `{alias}-local`, you can install packages
from local pear host when you are offline.

For example:

    $ sudo pear channel-discover pear-local
    $ sudo pear install pear-local/Archive_Tar
