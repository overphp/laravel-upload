<?php
/**
 * Created by PhpStorm.
 * User: overphp
 * Github: https://github.com/overphp
 * Date: 2018/7/16
 * Time: 下午7:55
 */

namespace Overphp\Upload;

use Overphp\Upload\Contracts\UploadInterface;

class UploadFile implements UploadInterface
{
    /**
     * 文件上传
     *
     * @param array $config
     * @return array
     */
    public function upload(array $config)
    {
        logger('upload params', request()->all());

        $storage = new UploadStorage(
            $config['maxSize'],
            $config['allowFiles'],
            $config['pathFormat'],
            config('upload.disk')
        );

        // 获取上传文件
        $file = request()->file($config['fieldName']);

        // 校验并保存文件
        if ($storage->validateUploadedFile($file)) {
            $storage->uploadUploadedFile($file);
        };

        return $storage->getFileInfo();
    }
}
