<?php

namespace Zandervdm\Gitception;

use Illuminate\Support\Facades\Facade;

class GitceptionFacade extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'zandervdm.gitception';
    }
}

