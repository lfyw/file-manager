<?php


namespace Lfyw\FileManager;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\ServiceProvider;

class FileManagerProvider extends ServiceProvider
{
    protected $defer = true;

    public function boot()
    {
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
        $this->registerWithFiles();
    }

    public function register()
    {
        $this->publishes([
            __DIR__ . '/../database/migrations/' => database_path('migrations')
        ], 'migrations');
        $this->publishes([
            __DIR__ . '/../config/file-manager.php' => config_path('file-manager.php'),
        ], 'config');
        $this->mergeConfigFrom(
            __DIR__ . '/../config/file-manager.php', 'file-manager'
        );
    }

    protected function registerWithFiles()
    {
        Builder::macro('withFiles', function ($type = null) {
            return $this->when($type, function ($builder) use ($type) {
                return $builder::with(['files' => function ($builder) use ($type) {
                    return is_array($type) ? $builder->where('fileables.type','in', $type) : $builder->where('fileables.type', $type);
                }]);
            }, function ($builder) {
                return $builder::with('files');
            });
        });
    }
}