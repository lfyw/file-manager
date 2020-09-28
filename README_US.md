<h1 align="center"> file-manager </h1>

<p align="center"> A file manager.</p>


## Installing

```shell
$ composer require littledragoner/file-manager
```

## Usage

* Migration

First, you should use migrate command to generate the necessary tables:
 ```shell script
$ php artisan migrate
```
You will get the ```files``` table and ```file_model```table after this command.

If you want to make some changes to the migrations, you can also publish the migrations by:
```shell script
$ php artisan vendor:publish --tag='migrations'
```
 
* Config

Publish config by:
```shell script
$ php artisan vendor:config --tag='config'
```

`path` means the directory that the files will be saved
`clear_sync_path` means delete the detached files after sync files or not.
```php
return [
    'path' => env('FILE_PATH', 'public/uploads'),
    'clear_sync_file' => env('FILE_CLEAR', true)
];
``` 

* Upload

Now, you can upload file like this:
```php
class FilesController extends Controller
{
    public function store(Request $request)
    {
        return \Littledragoner\FileManager\Models\File::upload($request->file('file'));
    }
}
```
It will return a new `\Littledragoner\FileManager\Models\File` Model
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
* Sync Files

Use `HasFiles` trait in your model
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

Use `$user->syncFiles([3,4]` to sync files.
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
If `User` model has multiple files.Pass a `type` mark to the second parameter:
```php
$user->syncFiles([3,4], 'avatar');
```
Get a user's files like this:
```
$user = User::find(1);
$user->loadFiles('avatar');
```
Use eager loading in eloquent builder:
```
User::withFiles('avatar')->get()
```

## License

MIT