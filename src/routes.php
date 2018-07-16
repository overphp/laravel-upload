<?php
/**
 * Created by PhpStorm.
 * User: overphp
 * Github: https://github.com/overphp
 * Date: 2018/7/16
 * Time: 下午11:32
 */

Route::group(['prefix' => 'upload', 'namespace' => 'Overphp\Upload'], function () {
    Route::post('file', 'UploadController@file');
    Route::post('image', 'UploadController@image');
});