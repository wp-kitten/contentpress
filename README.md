# ContentPress
WordPress-like application using the Laravel Framework.

## Reset to default state
```
//#! Just the db structure
php artisan cp:install --n

//#! DB structure + the default duumy data
php artisan cp:install --n --s

//#! Installs the ContentPress Default Theme
php artisan cp:install --t
```
 


## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The ContentPress CMS is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).


## Media Modal

**HTML** (required IDs & classes)
```html
<div class="js-image-preview">
    <input type="hidden" name="__category_image_id" id="__category_image_id" value=""/>
    <img id="__category_image_preview"
         src=""
         alt=""
         class="thumbnail-image hidden"/>
    <span class="js-preview-image-delete" title="{{__('a.Remove image')}}">&times;</span>
</div>
<p>
    <button type="button"
            class="btn btn-primary mr-2"
            data-image-target="#__category_image_preview"
            data-input-target="#__category_image_id"
            data-toggle="modal"
            data-target="#mediaModal">
        {{__('a.Select image')}}
    </button>
</p>
```

**PHP**

use **contentPressEnqueueMedia()** function to:
* Load dependent scripts
* Inject the Media Modal markup 

## Themes & Plugin updates
The core offers the possibility of automatic updates for themes and plugins.

Screen: **Dashboard > Updates**

The core requires the URL to check for updates in the theme or plugin config file to which it will send a POST request:
* POST['name'] = theme or plugin directory name

and it expects a JSON response in the following format:

**Success**
```php
[
    'data' => [
        //Ex theme: https://example.com/theme/material.zip
        //Ex plugin: https://example.com/theme/hello-world.zip

        'url' => 'https://example.com/theme/plugin-dir-name.zip',
        'version' => '1.3',
    ],
    'code' => 200,
];
```
**Error**
```php
[
    'errors' => [
        [
            'title' => 'Theme/Plugin not found',
            'description' => 'The specified theme/plugin was not found.',
        ],
        //...other errors here if necessary
    ],
    'code' => 404,
];
```
