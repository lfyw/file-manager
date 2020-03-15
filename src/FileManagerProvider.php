<?php


namespace littledragoner\FileManager;

use Illuminate\Support\ServiceProvider;

class FileManagerProvider extends ServiceProvider
{
    protected $defer = true;

    public function boot()
    {
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
    }

    public function register()
    {
        $this->app->singleton(FileManager::class, function () {
            return new FileManager();
        });

        $this->app->alias(FileManager::class, 'fileManager');
    }

    public function provides()
    {
        return [
            FileManager::class, 'fileManager'
        ];
    }
}