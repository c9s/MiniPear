MiniPear - PEAR Channel Local Mirror Tool
=========================================
MiniPear can create local pear channel mirrors for offline usage.

<a target="_blank" href="https://github.com/c9s/MiniPear/raw/master/static/01.png">
<img src="https://github.com/c9s/MiniPear/raw/master/static/01.png" width="300"/>
</a>
<br/>
<a target="_blank" href="https://github.com/c9s/MiniPear/raw/master/static/02.png">
<img src="https://github.com/c9s/MiniPear/raw/master/static/02.png" width="300"/>
</a>

## Install through Pear channel

    $ sudo pear channel-discover pear.corneltek.com
    $ sudo pear install corneltek/MiniPear

## Usage

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
