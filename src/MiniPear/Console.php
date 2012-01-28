<?php
namespace MiniPear;
use CLIFramework\Application as ConsoleApplication;

class Console extends ConsoleApplication
{
    const app_name = 'minipear';
    const app_version = '0.0.2';

    function init()
    {
        parent::init();
        $this->registerCommand('mirror');
    }

}

