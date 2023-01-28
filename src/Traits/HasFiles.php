<?php


namespace Lfyw\FileManager\Traits;


use Illuminate\Support\Facades\DB;
use Lfyw\FileManager\Models\File;

trait HasFiles
{
    protected array $fileStash = [];

    protected bool $forceSync = false;

    /**
     * 多态关联
     * @return mixed
     */
    public function files()
    {
        return $this->morphToMany(File::class, 'fileable')->withPivot('type');
    }

    /**
     * 新增关联文件（特定类型/无类型）
     * @param mixed $param 文件
     * @param string|null $type 指定文件类型关联
     * @return mixed
     */
    public function attachFiles($param = null, string $type = null)
    {
        $this->addFiles($param, $type);
        return $this->files()->attach($this->fileStash);
    }

    /**
     * 同步关联文件
     * @param mixed $param 文件
     * @param string|null $type 指定文件类型
     * @param bool $clear 同步后是否删除源文件
     * @param bool $onlyCurrent 是否仅同步当前类型的文件（如果为true，则不会影响已关联的其他类型的文件）
     * @return bool
     */
    public function syncFiles($param = null, string $type = null, $clear = false, $onlyCurrent = true)
    {
        if ($onlyCurrent) {
            $this->syncKeepOtherType($type);
        }
        $this->addFiles($param, $type);
        if ($this->fileStash || $this->forceSync === true ) {
            $changes = $this->files()->sync($this->fileStash);
            $this->destroyFileAfterSync($changes, $clear);
            return true;
        }
        return false;
    }

    /**
     * 同步当前类型文件
     * @param mixed $param 文件
     * @param string|null $type 指定文件类型
     * @param bool $clear 同步后是否删除源文件
     * @return bool
     */
    public function syncOnlyCurrentTypeFiles($param = null, string $type = null, $clear = false)
    {
        return $this->syncFiles($param, $type, $clear, true);
    }

    /**
     * 仅同步文件，不删除文件
     * @param mixed $param 文件
     * @param string|null $type 指定文件类型
     * @param bool $clear 同步后是否删除源文件
     * @return bool
     */
    public function syncFilesWithoutDetaching($param = null, string $type = null, $clear = false)
    {
        $this->addFiles($param, $type);
        if ($this->fileStash || $this->forceSync === true) {
            $changes = $this->files()->syncWithoutDetaching($this->fileStash);
            $this->destroyFileAfterSync($changes, $clear);
            return true;
        }
        return false;
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

    /**
     * 取消关联（当前类型/全部类型）
     * @param mixed $param 指定id取消关联
     * @param string|null $type 指定类型取消关联
     * @return mixed
     */
    public function detachFiles($param = null, string $type = null)
    {
        $fileIds = DB::table('fileables')
            ->when(filled($param), function ($builder) use ($param) {
                return is_array($param) ? $builder->whereIn('file_id', $param) : $builder->where('file_id', $param);
            })
            ->when(filled($type), function ($builder) use ($type) {
                return $builder->where('type', $type);
            })
            ->where('fileable_id', $this->id)
            ->where('fileable_type', get_class($this))
            ->pluck('file_id')
            ->toArray();

        $this->files()->detach($fileIds);
        File::destroy($fileIds);

        return $fileIds;
    }


    /**
     * addFiles
     * @param mixed $param
     * @param mixed $type
     * @return void
     */
    public function addFiles($param = null, string $type = null): self
    {
        $this->fileStash += $this->qualifyParam($param, $type);
        return $this;
    }

    public function loadFiles($type = null)
    {
        return $this->load(['files' => function ($builder) use ($type) {
            $builder->when($type, function ($builder) use ($type) {
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
     * @param mixed $param file param
     * @param mixed $type file type
     * @return array formed file array
     * form 1 []
     * form 3 [1 => 'avatar', 2 => 'background']
     */
    protected function qualifyParam($param = null, string $type = null): array
    {
        if (!$param) {
            return [];
        }
        if (!is_array($param)) {
            return $type ? [$param => ['type' => $type]] : [$param => ['type' => null]];
        }
        if ($type) {
            return array_fill_keys($param, ['type' => $type]);
        }
        if ($this->arrayIsAssoc($param)) {
            $newParam = [];
            foreach ($param as $key => $value) {
                $newParam[$key] = ['type' => $value];
            }
            return $newParam;
        }

        return array_fill_keys($param, ['type' => null]);
    }

    /**
     * 数组是否是关联数组
     * @param $array
     * @return bool
     */
    private function arrayIsAssoc($array): bool
    {
        if (!is_array($array)) {
            return false;
        }
        $keys = array_keys($array);

        return $keys != array_keys($keys);
    }

    /**
     * 同步后删除文件
     * @param array $changes 发生变化的文件id
     * @param bool $clear 是否删除源文件
     * @return void
     */
    private function destroyFileAfterSync($changes, $clear = false): void
    {
        if (config('file-manager.clear_sync_file') || $clear) {
            foreach ($changes['detached'] as $changeId) {
                File::destroy($changeId);
            }
        }
    }

    /**
     * 保留除当前类型外的其他所有类型文件
     * @param string $type 类型
     * @return void
     */
    private function syncKeepOtherType($type)
    {
        if ($type) {
            //获取除了类型之外的其他文件，这些文件需要作为非改动字段自动录入
            $currentFiles = DB::table('fileables')
                ->where('fileable_id', $this->id)
                ->where('fileable_type', get_class($this))
                ->when(filled($type), function ($builder) use ($type) {
                    return $builder->where('type', '<>', $type)->orWhereNull('type');
                })
                ->get();
            foreach ($currentFiles as $currentFile) {
                $this->addFiles($currentFile->file_id, $currentFile->type);
            }
        }
    }

}