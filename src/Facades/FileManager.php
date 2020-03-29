<?php


namespace Littledragoner\FileManager\Facades;

use Illuminate\Support\Facades\Facade;

class FileManager extends Facade
{
    protected static function getFacadeAccessor()
    {
        return new \Littledragoner\FileManager\FileManager();
    }
}