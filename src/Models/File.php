<?php


namespace Littledragoner\FileManager\Models;

use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Littledragoner\FileManager\Observers\FileObserver;

class File extends Model
{
    protected $guarded = [];

    protected $casts = [
        'extra' => 'array'
    ];

    protected $hidden = [
        'pivot'
    ];

    public static function boot()
    {
        parent::boot();

        static::observe(FileObserver::class);
    }

    /**
     * Upload and store a file
     * @param $file
     * @param bool $changeName
     * @return array
     */
    public static function upload($file)
    {
        throw_unless($file, new FileNotFoundException('File not found'));

        //保存文件
        $path = Storage::putFile(config('file-manager.path'), $file);

        return static::create([
            'original_name' => $file->getClientOriginalName(),//原文件名
            'save_name' => str_replace(config('file-manager.path') . '/', '', $path),
            'path' => str_replace('public/', '', $path),
            'url' => Storage::url($path),
            'extension' => $file->getClientOriginalExtension(),//原扩展名
            'extra' => [
                'client_extension' => $file->clientExtension(),//扩展名
                'clientMineType' => $file->getClientMimeType(),//mime类型
                'extension' => $file->extension(),//扩展名
                'size' => $file->getSize() //文件大小
            ]
        ]);
    }

}