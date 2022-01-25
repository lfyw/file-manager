<?php


namespace Lfyw\FileManager\Facades;

use Illuminate\Support\Facades\Facade;

class FileManager extends Facade
{
    protected static function getFacadeAccessor()
    {
        return new \Lfyw\FileManager\FileManager();
    }
}