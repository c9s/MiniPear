<?php
/*
 * This file is part of the CLIFramework package.
 *
 * (c) Yo-An Lin <cornelius.howl@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */
namespace CLIFramework;

class Utils
{

    /**
     * translate command name to class name
     *
     * so something like:   to-list will be ToListCommand
     *
     * */
    public static function translateCommandClassName($command)
    {
        $args = explode('-',$command);
        foreach($args as & $a)
            $a = ucfirst($a);
        $subclass = join('',$args) . 'Command';

        return $subclass;
    }

}
