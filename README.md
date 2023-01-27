<h1 align="center"> file-manager </h1>

<p align="center"> A file manager.</p>


## 安装

```shell
$ composer require lfyw/file-manager
```
## 使用


### 数据库迁移

执行数据库迁移:
 ```shell script
$ php artisan migrate
```
如果需要对数据表做修改，可以导出迁移文件:
```shell script
$ php artisan vendor:publish --tag='migrations'
```
 
### 配置

导出配置文件:
```shell script
$ php artisan vendor:publish --tag='config'
```

`path`是文件存放目录；`clear_sync_file`是指同步完文件，是否删除同步中失效的文件，建议`true`
```php
return [
    'path' => env('FILE_PATH', 'public/uploads'),
    'clear_sync_file' => env('FILE_CLEAR', true)
];
``` 

### 文件上传

**upload($file, $keepOriginalName = false, $guessExtension = true)**

像下面这样来上传文件。第一个参数是上传的文件;第二个参数是上传时是否以原文件名进行保存，默认会重新命名;第三个参数是是否根据文件 MIME 类型推测文件后缀，默认`true`, 如果要保存文件的原后缀名请改为`false`
```php
class FilesController extends Controller
{
    public function store(Request $request)
    {
        return \Lfyw\FileManager\Models\File::upload($request->file('file'), $keepOriginalName = false, $guessExtension = true);
    }
}
```
结果会返回一个`File`模型:
```json
{
    "original_name": "卡佐科技开放平台接入协议V1.5.docx",
    "save_name": "nxSwybO01e6hLooUIS2ClOzxhV1Mhw4easqx2guz.docx",
    "path": "uploads/nxSwybO01e6hLooUIS2ClOzxhV1Mhw4easqx2guz.docx",
    "url": "/storage/uploads/nxSwybO01e6hLooUIS2ClOzxhV1Mhw4easqx2guz.docx",
    "extension": "docx",
    "extra": {
        "client_extension": "docx",
        "clientMineType": "application/vnd.openxmlformats-officedocument.wordprocessingml.document",
        "extension": "docx",
        "size": 34003
    },
    "updated_at": "2020-09-28T13:16:56.000000Z",
    "created_at": "2020-09-28T13:16:56.000000Z",
    "id": 17
}
```
### 文件关联

在目标模型文件中引用`HasFiles`trait
```php
<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Lfyw\FileManager\Traits\HasFiles;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasFiles;
```
#### 新增文件关联

> **attachFiles($param = null, string $type = null)**

```php
$user = User::find(1);
$user->attachFiles();//空参数或者会被判定为false的参数(如：[]，null)什么都不会做，意味着你无需额外判定参数是否存在或是否为空
$user->attachFiles(1);//关联文件 id 为 1 的文件
$user->attachFiles([1,2]);//可以同时关联多个文件，传递文件的 id 数组
$user->attachFiles(1, 'avatar');//关联一个被标识为 avatar 的文件
$user->attachFiles([1 => 'avatar', 2 => 'background'])//同时关联多个不同标识的文件
```

#### 同步文件关联

> **syncFiles($param = null, string $type = null, $clear = true, $onlyCurrent = true)**

接受一个参数替换原本的关联，未在参数中的原关联会被移除 。如果`$onlyCurrent`的值为 true,则同步关联仅同步当前`type`的关联文件，而不会影响其他`type`。

```php
$user = User::find(1);
$user->syncFiles();//空参数或者会被判定为false的参数(如：[]，null)什么都不会做，意味着你无需额外判定参数是否存在或是否为空
$user->syncFiles(1);//同步关联文件 id 为 1 的文件
$user->syncFiles([1,2]);//可以同步关联多个文件，传递文件的 id 数组
$user->syncFiles(1, 'avatar');//同步关联一个被标识为 avatar 的文件
$user->syncFiles([1 => 'avatar', 2 => 'background'])//同步关联多个不同标识的文件

```
#### 同步当前类型文件关联

> **syncOnlyCurrentTypeFiles($param = null, string $type = null, $onlyCurrent = true)**

等同于`syncFiles($onlyCurrent = true)`

#### 同步不移除

> **syncFilesWithoutDetaching($param = null, string $type = null, $clear = false)**

用法同 `syncFiles()` 一致，同步的时候不会移除原先的关联文件

#### 移除关联

> **detachFiles($param = null, string $type = null)**

```php
$user = User::find(1);
$user->detachFiles(1);//移除文件 id 为 1 的关联
$user->detachFiles([1,2]);//移除文件 id 为 1 和 2 的关联
$user->detachFiles(type: 'avatar');//移除标识为 `avatar` 的关联
$user->detachFiles();//移除所有关联
```

#### 追加文件

> **addFiles($param = null, string $type = null)**

```php
$user = User::find(1);
//追加文件
$user->addFiles(1);
$user->addFiles([2]);
$user->attachFiles();
//兼容链式调用与方法本来的所有传参形式：
$user->addFiles([11 => 'avatar'])->attachFiles(12, 'background');
//同样适用于 syncFiles() syncFilesWithoutDetaching()
$user->addFiles([7 => 'avatar'])->syncFiles(9, 'background');
```

#### 强制同步

> **forceSync(bool$param = true)**

当使用`syncFiles()`同步文件时，方法会默认对参数做空判断，如果参数为空则不执行任何操作，从而避免手动判断文件参数是否为空。但在某些情况下，如编辑的时候取消了图片，此时需要将空参数也参与同步关联,可以使用该方法进行强制同步:

```php
public function store(Request $request)
{
    $user = User::find(1);
    $user->forceSync()->syncFiles($request->file_ids);//如果 $request->file_ids 为空，则同步后不关联任何文件。
}
```

注意:由于`syncFilesWithoutDetaching()`是同步不移除，所以`forceSync()`对`syncFilesWithoutDetaching`无效.

#### 预加载

```php
$user = User::withFiles('avatar')->get();
```
#### 延迟预加载

```php
$user = User::find(1);
$user->loadFiles('avatar');
```

## License

MIT
