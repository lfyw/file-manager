<?php


namespace Littledragoner\FileManager\Traits;


use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Littledragoner\FileManager\Models\File;

trait HasFiles
{
    /**
     * Sync file between specify model and file
     * @param $fileIds
     * @param string|null $type
     * @return bool
     */
    public function syncFiles($fileIds, ?string $type = null): bool
    {
        if (!is_array($fileIds)) {
            $fileIds = [$fileIds];
        }

        $this->isInTable($fileIds);
        $values = ['model_type' => static::class];
        $values = $type ? array_merge($values, ['file_type' => $type]) : $values;

        $changes = $this->files()->sync(array_fill_keys($fileIds, $values));

        $this->destroyFileAfterSync($changes);

        return true;
    }

    /**
     * Relations with files
     * @return BelongsToMany
     */
    public function files(): BelongsToMany
    {
        return $this->belongsToMany(File::class, 'file_model', 'model_id', 'file_id', 'id', 'id')->withPivot(['model_id', 'file_type'])->wherePivot('model_type', static::class);
    }

    /**
     * Load relation files on a specify model
     * @param null $type
     * @return mixed
     */
    public function loadFiles($type = null)
    {
        return $type
            ? $this->load(['files' => function ($builder) use ($type) {
                return $builder->where('file_type', $type);
            }])
            : $this->load('files');
    }

    public function detachFiles(): void
    {
        $this->files()->each(function ($file){
            $file->delete();
        });
        $this->files()->sync([]);
    }

    /**
     * Destroy file after sync
     * @param $changes
     */
    private function destroyFileAfterSync($changes): void
    {
        if (config('file-manager.clear_sync_file')) {
            File::destroy($changes['detached']);
        }
    }

    /**
     * Check file id is in files table
     * @param $fileIds
     */
    protected function isInTable($fileIds): void
    {
        File::findOrFail($fileIds);
    }

}