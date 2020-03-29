<?php


namespace Littledragoner\FileManager\Observers;

use Littledragoner\FileManager\Models\File;
use Illuminate\Support\Facades\Storage;

class FileObserver
{
    /**
     * Handle the file "deleted" event.
     *
     * @param  littledragoner\FileManager\Models\File  $file
     * @return void
     */
    public function deleted(File $file)
    {
        Storage::disk('public')->delete($file->path);
    }
}