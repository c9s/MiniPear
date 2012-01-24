<?php
namespace MiniPear;
use CLIFramework\Application as ConsoleApplication;

class Console extends ConsoleApplication
{

    function init()
    {
        parent::init();
        $this->registerCommand('mirror');
    }

}

