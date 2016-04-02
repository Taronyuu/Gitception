<?php namespace Zandervdm\Gitception;

class Facade extends \Illuminate\Support\Facades\Facade
{
    protected static function getFacadeAccessor()
    {
        return 'zandervdm.gitception';
    }
}