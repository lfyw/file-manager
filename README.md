<h1 align="center"> file-manager </h1>

<p align="center"> A file manager.</p>


## Installing

```shell
$ composer require littledragoner/file-manager -vvv
```

## Usage

* Migration

First, you should use migrate command to generate the necessary tables:
 ```shell script
$ php artisan migrate
```
You will get the ```files``` table and ```file_model```table after this command.

* Upload

Now, you can upload file like this:
```php
class FilesController extends Controller
{
    /**
     * @param Request $request
     * @param FileManager $fileManager
     */
    public function store(Request $request, FileManager $fileManager)
    {
        return $fileManager->store($request->file('file'));
    }
}
```
The file will be stored in ```storage_path('app/public/temporary')``` directory and it's named randomly. Also,it would return some message like this.
```json
{
    "id": 1, 
    "save_name": "7Qcyik0OEFM3dF1C18I2mP0v6zuymPqnCtUIR9U6.jpeg",
    "original_name": "avator.jpg",
    "url": "/storage/temporary/7Qcyik0OEFM3dF1C18I2mP0v6zuymPqnCtUIR9U6.jpeg"
}
```

* Associate

If you want to use this file on some model, you should create an method in the model which you want to associate this file with.
```php
    public function files()
    {
        return $this->belongsToMany(File::class, 'file_model', 'model_id', 'file_id', 'id', 'id')->withPivot(['model_type', 'file_type'])->wherePivot('model_type', 'user')->wherePivot('file_type', 'test');
    }
```
The ```model_type``` means the model this file associate with so that you can get that file by the model method.The ```file_type``` field means different field in one model. The below method shows that:
 1. User model associates with file model
 2. This method is associated with user's test field.
 
 * Store or Update model
 
 Now, everything is easy. If I want to add a user's avatar,i just need do this:
 ```php
    public function store(FileManager $fileManager)
    {
        $user = User::find(1);
        $fileManager->sync($user, 'user', 9, 'test');
    }
```  
That means this user model has one avatar whose id is 9.I can get this user's avatar like this:
```php
    $user = User::find(1);
    dd($user->files);
```
## License

MIT