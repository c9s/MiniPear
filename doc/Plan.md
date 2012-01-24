# MiniPear Plan

For local pear channel mirror.

## Requirement

- Should support current pear channel ecosystem
    - pear tool can mirror through local http server and discovered.
    - need a real mirror of pear channel.
- Should support mini local filesystem mirror path
    - save a local channel list.
    - lookup by channel host (channel name).
    - provide a global pear channel source list (another github project)
    - channel source / package infomations can be retrieveed through 
       json service or so called RESTful service, should should be simple enough.
- minipear filesystem should be able to distribute through ssh protocal or
  http, https protocal.

- Packages should be easily searched.
- Packages should be easily injected.

- Should support proxy access feature (with CURL)
- Should support basic authentication mech.
- Should provide a minipear configuration file.

## Basic MiniPear Concepts

A channel host is related to an URI, 
which can be a resource of git clone path, hg clone path, or http, ssh, path.

Channel name or host name:

- pear.php.net
- local.pear

possible URI formats:

- git@github.com:c9s/minipear\_list.git
- http://domain.com/path/to/channel/
- ftp://domain.com/path/to/channel/
- https://domain.com/path/to/channel/
- c9s@host.com:path/to/channel

And with a resource handler:

- git:
- hg:
- ssh:
- http:
- https:
- ftp:

A basic channel directory structure is like:

    ~/.minipear 
        /minipear
            /channel1
                /releases.json
                /packages/u/ui/universal-0.1.tar.gz
                /packages/u/ui/universal-0.2.tar.gz
                /packages/u/ui/universal-0.4.tar.gz
            /user1
                .....
            /user2
        /pear
            /channel1
                {general pear channel structure}

## Usage

To add a pear channel:

    $ minipear add {channel}

To update mirror:

    $ minipear update

To inject a package into a channel:

    $ minipear inject {channel name} {release}

To get a package from specifield user

    $ minipear get minipear_channel_name/c9s/Universal

    $ minipear get minipear_channel_name/foo/Universal

    $ minipear get pear_channel_name/Universal

## A General PEAR channel traversal to find a package

* get lookup channel.xml to get rest url.
* get rest/p/packages.xml for package list
* get rest/r/{package}/allreleases2.xml to get package release version info
    r tag for each release
        version
        stability
        php minimum require
* to get dependency info:
    r/cliframework/deps.0.0.2.txt
* c/Default/packagesinfo.xml
    this contains package and dependency infomations

## Implementation Plan

* implement a basic pear channel mirror tool.
* add local suffix to channel to avoid collision.

