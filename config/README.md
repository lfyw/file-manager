<h1 align="center"> file-manager </h1>

<p align="center"> A file manager.</p>


## 安装

```shell
$ composer require littledragoner/file-manager
```

## 使用

* 数据库迁移

执行数据库迁移:
 ```shell script
$ php artisan migrate
```
如果需要对数据表做修改，可以导出迁移文件:
```shell script
$ php artisan vendor:publish --tag='migrations'
```
 
* 配置

导出配置文件:
```shell script
$ php artisan vendor:config --tag='config'
```

`path`是文件存放目录；`clear_sync_file`是指同步完文件，是否删除同步中失效的文件，建议`true`
```php
return [
    'path' => env('FILE_PATH', 'public/uploads'),
    'clear_sync_file' => env('FILE_CLEAR', true)
];
``` 

* 上传文件

像下面这样来上传文件:
```php
class FilesController extends Controller
{
    public function store(Request $request)
    {
        return \Littledragoner\FileManager\Models\File::upload($request->file('file'));
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
* 同步文件

在目标模型文件中引用`HasFiles`trait
```php
<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Littledragoner\FileManager\Traits;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasFiles;
```

使用`$user->syncFiles([3,4])`同步文件，参数是需要同步的文件`id`数组
```shell script
<?php

namespace App\Http\Controllers;

use App\Models\User;

class UsersController extends Controller
{
    public function store(Request $request)
    {
        $user = User::find(1);
        return response()->json(['data' => $user->syncFiles([3,4])]);
    }
}
```
如果这个模型上有多种类型的文件，如用户有头像、有个人简历等多种附件，则需要给每种类型的附件增加一个标识字段作为方法的第二个参数。
```php
$user->syncFiles([3,4], 'avatar');
```
可以像下面这样获取某个模型关联的特定类型文件，不加参数返回这个模型关联的所有文件
```
$user = User::find(1);
$user->loadFiles('avatar');
```
可以直接在`ORM`上调用`withFiles`来预加载关联模型，可以传递相关标识参数预加载特定类型的文件
```
User::withFiles('avatar')->get()
```

## License

MIT