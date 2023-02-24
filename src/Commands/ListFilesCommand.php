<?php

namespace Lfyw\FileManager\Commands;

use Illuminate\Console\Command;
use Lfyw\FileManager\Models\File;

//todo 列出所有文件
class ListFilesCommand extends Command
{
    protected $name = 'file-manager:list';

    protected $description = 'file-manager:list';

    public function handle()
    {
        $this->table(
            ['id', 'original_name',  'url', 'extension', 'created_at', 'updated_at'],
            File::all(['id', 'original_name', 'url', 'extension', 'created_at', 'updated_at'])->toArray()
        );
    }
}