<?php
/**
 * Created by PhpStorm.
 * User: overphp
 * Github: https://github.com/overphp
 * Date: 2018/7/16
 * Time: 下午7:53
 */

namespace Overphp\Upload;

use Illuminate\Support\ServiceProvider;

class UploadServiceProvider extends ServiceProvider
{
    public function boot()
    {
        // 注册视图
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'upload');
        // 注册语言包
        $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'upload');

        $this->loadRoutesFrom(__DIR__.'/routes.php');

        $this->publishes([
            __DIR__ . '/../config/upload.php' => config_path('upload.php'),
            __DIR__ . '/../resources/views' => base_path('resources/views/vendor/upload'),
            __DIR__ . '/../resources/lang' => resource_path('lang/vendor/upload'),
        ], 'laravel-upload');

        $this->publishes([
            __DIR__ . '/../resources/assets' => public_path(config('upload.assets_path')),
        ], 'laravel-upload-assets');
    }
}
