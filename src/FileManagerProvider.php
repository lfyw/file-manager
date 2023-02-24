<?php


namespace Lfyw\FileManager;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\ServiceProvider;
use Lfyw\FileManager\Commands\ListFilesCommand;
use Lfyw\FileManager\Commands\PruneFilesCommand;

class FileManagerProvider extends ServiceProvider
{
    protected $defer = true;

    public function boot()
    {
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
        $this->registerWithFiles();
        $this->registerWithFilesCount();
        $this->registerConsoleCommands();
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

    protected function registerWithFilesCount()
    {
        Builder::macro('withFilesCount', function ($type = null) {
            return $this->when($type, function ($builder) use ($type) {
                return $builder::withCount(['files' => function ($builder) use ($type) {
                    return is_array($type) ? $builder->where('fileables.type','in', $type) : $builder->where('fileables.type', $type);
                }]);
            }, function ($builder) {
                return $builder::withCount('files');
            });
        });
    }

    protected function registerConsoleCommands()
    {
        $this->commands([
//            PruneFilesCommand::class,
            ListFilesCommand::class
        ]);
    }
}