<?php


namespace littledragoner\FileManager\Models;

use littledragoner\FileManager\Observers\FileObserver;
use Illuminate\Database\Eloquent\Model;

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
}