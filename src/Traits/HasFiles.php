<?php


namespace Lfyw\FileManager\Traits;


use Lfyw\FileManager\Models\File;
use Illuminate\Support\Facades\DB;

trait HasFiles
{
    protected array $fileStash = [];

    protected bool $forceSync = false;

    public function files()
    {
        return $this->morphToMany(File::class, 'fileable')->withPivot('type');
    }

    public function detachFiles($param = null, string $type = null)
    {
        $fileIds =  DB::table('fileables')
        ->when(filled($param), function($builder) use ($param){
            return is_array($param) ? $builder->whereIn('file_id', $param) : $builder->where('file_id', $param);
        })
        ->when(filled($type), function($builder) use ($type){
            return $builder->where('type', $type);
        })
        ->pluck('file_id')
        ->toArray();

        $this->files()->detach($fileIds);
        File::destroy($fileIds);

        return $fileIds;
    }

    public function syncFilesWithoutDetaching($param = null, string $type = null)
    {
        $this->addFiles($param, $type);
        if($this->fileStash || $this->forceSync === true){
            $changes = $this->files()->syncWithoutDetaching($this->fileStash);
            $this->destroyFileAfterSync($changes);
            return true;
        }
        return false;
    }

    public function syncFiles($param = null, string $type = null)
    {
        $this->addFiles($param, $type);
        if($this->fileStash || $this->forceSync === true){
            $changes = $this->files()->sync($this->fileStash);
            $this->destroyFileAfterSync($changes);
            return true;
        }
        return false;
    }

    public function attachFiles($param = null, string $type = null)
    {
        $this->addFiles($param, $type);
        return $this->files()->attach($this->fileStash);
    }

    /**
     * addFiles
     * @param  mixed $param
     * @param  mixed $type
     * @return void
     */
    public function addFiles($param = null, string $type = null):self
    {
        $this->fileStash += $this->qualifyParam($param, $type);
        return $this;
    }

    /**
     * forceAttach
     * Force attach will delete the previous existing files.
     * @return void
     */
    public function forceSync(bool $param = true):self
    {
        $this->forceSync = $param;
        return $this;
    }

    public function loadFiles($type = null)
    {
        return $this->load(['files' => function($builder) use ($type){
            $builder->when($type, function($builder) use ($type){
                return is_array($type) ? $builder->whereIn('fileables.type', $type) : $builder->where('fileables.type', $type);
            });
        }]);
    }

    /**
     * qualifyParam
     * form 1 ($param = 1)
     * form 2 ($param = [1,2])
     * form 3 ($param = 1, 'avatar')
     * form 4 ($param = [1,2], 'avatar')
     * form 5 ($param = [1 => 'avatar', '2' => 'background'])
     * @param  mixed $param file param
     * @param  mixed $type file type
     * @return array formed file array
     * form 1 []
     * form 3 [1 => 'avatar', 2 => 'background']
     */
    protected function qualifyParam($param = null, string $type = null):array
    {
        if(!$param){
            return [];
        }
        if(!is_array($param)){
            return $type ? [$param => ['type' => $type]] : [$param => ['type' => null]];
        }
        if($type){
            return array_fill_keys($param, $type);
        }
        if($this->arrayIsAssoc($param)){
            $newParam = [];
            foreach($param as $key => $value){
                $newParam[$key] = ['type' => $value];
            }
            return $newParam;
        }

        return array_fill_keys($param, ['type' => null]);
    }

    private function arrayIsAssoc($array):bool
    {
        if(!is_array($array)){
            return false;
        }
        $keys = array_keys($array);

        return $keys != array_keys($keys);
    }

    private function destroyFileAfterSync($changes): void
    {
        if (config('file-manager.clear_sync_file')) {
            foreach ($changes['detached'] as $changeId) {
                File::destroy($changeId);
            }
        }
    }
}