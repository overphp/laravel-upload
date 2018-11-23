<?php
/**
 * Created by PhpStorm.
 * User: yangyang
 * Date: 2018/11/23
 * Time: 11:04 AM
 */

namespace Overphp\Upload;

class Upload
{
    /**
     * @param array $config
     * @return array
     */
    public function upload(array $config)
    {
        $upload = new UploadFile(config('upload.disk'));

        $file = request()->file($config['fieldName']);

        return $upload->upload(
            $file,
            $config['allowFiles'],
            $config['maxSize'],
            $config['pathFormat']
        );
    }
}
