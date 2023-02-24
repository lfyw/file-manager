<?php


namespace Lfyw\FileManager\Models;

use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Lfyw\FileManager\Observers\FileObserver;
use DateTimeInterface;

class File extends Model
{
    /**
     * Prepare a date for array / JSON serialization.
     *
     * @param  \DateTimeInterface  $date
     * @return string
     */
    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        if (config('file-manager.connection')) {
            $this->connection = config('file-manager.connection');
        }
    }

    protected $fillable = [
        'original_name', 'save_name', 'path', 'url', 'extension', 'extra', 'created_at', 'updated_at'
    ];

    protected $casts = [
        'extra' => 'array'
    ];

    public static function booted()
    {
        static::observe(FileObserver::class);
    }

    /**
     * Upload and store a file
     * @param $file
     * @param bool $ext 是否保存文件原后缀
     * @return File
     */
    public static function upload($file, $keepOriginalName = false, $guessExtension = true)
    {
        throw_unless($file, new FileNotFoundException('File not found'));

        $clientOriginalExtension = $file->getClientOriginalExtension();
        $clientOriginalName = $file->getClientOriginalName();
        $fileExtension = $file->extension();

        $extension = $guessExtension ? $fileExtension : $clientOriginalExtension;
        $filename = $keepOriginalName ? $clientOriginalName : Str::random(40) . '.' . $extension;

        $customFileDir = Str::before(storage_path('app/public'), 'public') . config('file-manager.path');
        if (!is_dir($customFileDir)) {
            mkdir($customFileDir, 0777, true);
        }

        $savePath = Storage::putFileAs(config('file-manager.path'), $file, $filename);
        $saveName = str_replace(config('file-manager.path') . '/', '', $savePath);
        $publicPath = str_replace('public/', '', $savePath);
        return static::create([
            'original_name' => $clientOriginalName,
            'save_name' => $saveName,
            'path' => $publicPath,
            'url' => Storage::url($savePath),
            'extension' => $extension,
            'extra' => [
                'client_extension' => $clientOriginalExtension,
                'client_mine_type' => $file->getClientMimeType(),
                'extension' => $fileExtension,
                'size' => $file->getSize(),
            ],
        ]);
    }

}