<?php
namespace MiniPear;
use CLIFramework\Application as ConsoleApplication;

class Console extends ConsoleApplication
{
    const name = 'minipear';
    const version = '1.0.0';

    function init()
    {
        parent::init();
        $this->registerCommand('mirror');
    }

}

