<?php


namespace littledragoner\FileManager;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use littledragoner\FileManager\Models\File;

class FileManager
{
    /**
     * @param $file
     * @param bool $changeName
     * @return array
     */
    public function store($file)
    {
        //原文件信息
        $clientOriginalExtension = $file->getClientOriginalExtension();//原扩展名
        $clientOriginalName = $file->getClientOriginalName();//原文件名
        //分析信息
        $clientExtension = $file->clientExtension(); //扩展名
        $clientMineType = $file->getClientMimeType();//mime类型
        $extension = $file->extension();//扩展名
        $size = $file->getSize();//文件大小
        //保存文件
        $temporaryPath = 'public/temporary';
        $path = $file->store($temporaryPath);
        $fileModel = new File();
        $fileModel->fill([
            'original_name' => $clientOriginalName,
            'save_name' => str_replace($temporaryPath . '/', '', $path),
            'path' => str_replace('public/', '', $path),
            'url' => Storage::url($path),
            'extension' => $clientOriginalExtension,
            'extra' => [
                'client_extension' => $clientExtension,
                'clientMineType' => $clientMineType,
                'extension' => $extension,
                'size' => $size
            ]
        ])->save();
        return [
            'id' => $fileModel->id,
            'save_name' => $fileModel->save_name,
            'original_name' => $fileModel->original_name,
            'url' => $fileModel->url,
        ];
    }

    /**
     * 移动文件从临时文件夹到正式文件夹
     * @param array $fileIds 文件id数组
     */
    protected function move(array $fileIds = [])
    {
        $formalPath = '/uploads/' . date("Ym/d", time()) . '/';
        File::findMany($fileIds)->each(function ($item) use ($formalPath) {
            if (Storage::disk('public')->exists($item->path)) {
                //1.移入正式文件夹
                Storage::move('public/' . $item->path, 'public/' . $formalPath . $item->save_name);
                //2.更改表相关内容
                $item->path = $formalPath . $item->save_name;
                $item->url = '/storage' . $formalPath . $item->save_name;
                $item->save();
            }
        });
    }

    /**
     * 同步文件的时候，根据中间表中detach的文件关联删除文件表的信息，进一步关联删除物理文件
     * @param string $modelType 关联模型类型
     * @param array|string $fileIds sync的文件id数组
     * @param string|null $fileType 字段类型
     */
    public function sync($model, $modelType, $fileIds, $fileType = null, $relation = 'files')
    {
        if (!is_null($fileIds)) {
            $fileIds = is_array($fileIds) ? $fileIds : [$fileIds];
            $changes = $model->$relation()->sync(sync_format_keys(array_filter($fileIds), ['model_type' => $modelType, 'file_type' => $fileType]));
            $this->move($changes['attached']);
            File::destroy($changes['detached']);
        }
    }
}
