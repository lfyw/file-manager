<?php


namespace Lfyw\FileManager\Observers;

use Illuminate\Support\Facades\Storage;
use Lfyw\FileManager\Models\File;

class FileObserver
{
    /**
     * Handle the file "deleted" event.
     *
     * @param File $file
     * @return void
     */
    public function deleted(File $file)
    {
        Storage::disk('public')->delete($file->path);
    }
}