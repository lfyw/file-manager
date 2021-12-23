<?php


namespace Littledragoner\FileManager\Traits;


use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Littledragoner\FileManager\Models\File;

trait HasFiles
{
    protected $syncParameters = [];

    /**
     * Sync file between specify model and file
     * @param $fileIds
     * @param string|null $type
     * @return bool
     */
    public function syncFiles($force = false): bool
    {
        if (!$this->syncParameters && $force == false){
            return false;
        }
        $changes = $this->files()->sync($this->syncParameters);

        $this->destroyFileAfterSync($changes);

        return true;
    }

    /**
     * @todo
     * Sync files without Detaching
     * @return void
     */
    public function syncFilesWithoutDetaching($fileIds, $fileType):bool
    {
        $this->loadFiles('files:id');
         if($files = $this->files){
             foreach ($files as $file){
                 if($fileType == $file->pivot->file_type) continue;
                 $this->addAttach($file->id, $file->pivot->file_type);
             }
         }
         return $this->addAttach($fileIds)->syncFiles();
    }

    /**
     * Add file to model.
     * @param $fileIds
     * @param string|null $type
     * @return $this
     */
    public function addAttach($fileIds, ?string $type = null): self
    {
        if (!$fileIds){
            return $this;
        }
        if (!is_array($fileIds)) {
            $fileIds = [$fileIds];
        }

        $this->isInTable($fileIds);
        $values = ['model_type' => static::class];
        $values = $type ? array_merge($values, ['file_type' => $type]) : $values;
        $this->syncParameters += array_fill_keys($fileIds, $values);
        return $this;
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

    /**
     * Detach related files
     */
    public function detachFiles($type = null): void
    {
        if (!$type){
            $this->syncParameters = [];
            $this->syncFiles(true);
        }
        $this->addAttach([], $type)->syncFiles(true);
    }

    /**
     * Destroy file after sync
     * @param $changes
     */
    private function destroyFileAfterSync($changes): void
    {
        if (config('file-manager.clear_sync_file')) {
            foreach ($changes['detached'] as $changeId) {
                File::destroy($changeId);
            }
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