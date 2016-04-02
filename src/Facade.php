<?php

namespace Zandervdm\Gitception;

class Facade extends \Illuminate\Support\Facades\Facade
{
    /**
     * getFacadeAccessor function.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'zandervdm.gitception';
    }
}